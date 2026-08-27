import { createRouter, createWebHistory } from 'vue-router'

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
    {
      path: '/',
      component: () => import('@/core/layout/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/modules/dashboard/DashboardPage.vue'),
          meta: { title: 'Dashboard', desc: 'Ringkasan operasional klinik Anda' },
        },
        {
          path: 'appointments',
          name: 'appointments',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Appointment', module: 'appointments', requiresAuth: true },
        },
        {
          path: 'patients',
          name: 'patients',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Pasien', module: 'patients', requiresAuth: true },
        },
        {
          path: 'emr',
          name: 'emr',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Rekam Medis (EMR)', module: 'emrs', requiresAuth: true },
        },
        {
          path: 'odontogram',
          name: 'odontogram',
          component: () => import('@/modules/odontogram/OdontogramPage.vue'),
          meta: { title: 'Odontogram', requiresAuth: true },
        },
        {
          path: 'treatments',
          name: 'treatments',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Perawatan', module: 'treatments', requiresAuth: true },
        },
        {
          path: 'billing',
          name: 'billing',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Billing & Invoice', module: 'invoices', requiresAuth: true },
        },
        {
          path: 'inventory',
          name: 'inventory',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Inventaris', module: 'inventory_items', requiresAuth: true },
        },
        {
          path: 'pharmacy',
          name: 'pharmacy',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Farmasi', module: 'pharmacy_items', requiresAuth: true },
        },
        {
          path: 'laboratory',
          name: 'laboratory',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Laboratorium', module: 'lab_orders', requiresAuth: true },
        },
        {
          path: 'radiology',
          name: 'radiology',
          component: () => import('@/modules/settings/RadiologyPage.vue'),
          meta: { title: 'Radiologi', requiresAuth: true },
        },
        {
          path: 'doctors',
          name: 'doctors',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Dokter', module: 'doctors', requiresAuth: true },
        },
        {
          path: 'employees',
          name: 'employees',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Karyawan', module: 'employees', requiresAuth: true },
        },
        {
          path: 'branches',
          name: 'branches',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Cabang', module: 'branches', requiresAuth: true },
        },
        {
          path: 'organization',
          name: 'organization',
          component: () => import('@/modules/settings/OrganizationPage.vue'),
          meta: { title: 'Organisasi', requiresAuth: true },
        },
        {
          path: 'users',
          name: 'users',
          component: () => import('@/modules/settings/UsersRolesPage.vue'),
          meta: { title: 'Users & Roles', requiresAuth: true },
        },
        {
          path: 'crm',
          name: 'crm',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'CRM', module: 'crm_contacts', requiresAuth: true },
        },
        {
          path: 'reports',
          name: 'reports',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Laporan', module: 'reports', requiresAuth: true },
        },
        {
          path: 'ai',
          name: 'ai',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'AI Assistant', module: 'ai_queries', requiresAuth: true },
        },
        {
          path: 'integrations',
          name: 'integrations',
          component: () => import('@/shared/components/ModulePage.vue'),
          meta: { title: 'Integrasi', module: 'integration_configs', requiresAuth: true },
        },
        {
          path: 'subscription',
          name: 'subscription',
          component: () => import('@/modules/subscription/SubscriptionPage.vue'),
          meta: { title: 'Subscription', desc: 'Kelola langganan, paket, dan billing klinik Anda.' },
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('@/modules/settings/SettingsPage.vue'),
          meta: { title: 'Settings', desc: 'Pengaturan profil klinik, invoice, dan informasi billing.' },
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

router.beforeEach((to, _from, next) => {
  // Check auth status via reactive user from useAuth composable
  // Since we use Sanctum HttpOnly cookies, we check user state indirectly
  // by attempting a profile fetch on protected routes. For now, allow all
  // navigation — the API will return 401 if unauthenticated and the app
  // will handle redirect.
  next()
})

export default router