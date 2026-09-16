import type { Component } from 'vue'

/**
 * Portal architecture
 * ===================
 * The platform exposes three role-tailored portals. Each portal is a distinct
 * UI/UX paradigm (layout, theme, navigation) that shows only the modules the
 * signed-in user is permitted to use.
 *
 *   admin        -> grouped sidebar,  full management console   (indigo)
 *   doctor       -> slim clinical rail, care-focused            (teal)
 *   receptionist -> top navigation,    front-desk focused       (blue)
 *
 * Visibility is the intersection of:
 *   1. the modules the portal declares for its audience, AND
 *   2. the modules the user actually holds a permission for.
 *
 * Backend permissions follow the "{domain}.{action}" convention seeded by
 * RolePermissionSeeder (e.g. "patient.view"). The dashboard module is always
 * granted via "dashboard.view".
 */

export type PortalId = 'admin' | 'doctor' | 'receptionist'

export interface ModuleDef {
  /** key used in nav/portals and as `route.meta.module` for ModulePage */
  key: string
  label: string
  icon: string
  /** router path segment under the portal prefix, e.g. "patients" */
  path: string
  /** backend permission required to see/open this module */
  permission: string
  /** page component (lazy-loaded); omitted for modules rendered per-portal */
  component?: () => Promise<{ default: Component }>
  /** extra route meta (ModulePage reads `meta.module` to pick its config) */
  meta?: Record<string, string>
}

export interface NavItem {
  label: string
  icon: string
  module: string
}

export interface NavGroup {
  title: string
  items: NavItem[]
}

export interface PortalDef {
  id: PortalId
  label: string
  /** short tagline shown in the portal chrome */
  tagline: string
  /** router prefix, e.g. "/admin" */
  prefix: string
  /** Spatie role names that resolve to this portal (first match wins) */
  roles: string[]
  /** theme tokens consumed by the portal layout */
  theme: {
    accent: string
    accentSoft: string
    sidebarBg: string
    sidebarText: string
    activeBg: string
    activeText: string
  }
  /** dashboard component for this portal */
  dashboard: () => Promise<{ default: Component }>
  /** navigation; item visibility is intersected with the user's permissions */
  nav: NavGroup[]
}

export const MODULES: Record<string, ModuleDef> = {
  dashboard: {
    key: 'dashboard',
    label: 'Dashboard',
    icon: 'home',
    path: 'dashboard',
    permission: 'dashboard.view',
    // No shared dashboard component: every portal ships its own tailored
    // dashboard (see PortalDef.dashboard), which router/index.ts resolves.
  },
  appointments: {
    key: 'appointments',
    label: 'Appointment',
    icon: 'calendar',
    path: 'appointments',
    permission: 'appointment.view',
    component: () => import('@/modules/appointments/AppointmentsPage.vue'),
  },
  patients: {
    key: 'patients',
    label: 'Pasien',
    icon: 'users',
    path: 'patients',
    permission: 'patient.view',
    component: () => import('@/modules/patients/PatientsPage.vue'),
  },
  emr: {
    key: 'emr',
    label: 'Rekam Medis',
    icon: 'file',
    path: 'emr',
    permission: 'medical_record.view',
    component: () => import('@/modules/emr/EmrPage.vue'),
  },
  odontogram: {
    key: 'odontogram',
    label: 'Odontogram',
    icon: 'tooth',
    path: 'odontogram',
    permission: 'odontogram.view',
    component: () => import('@/modules/odontogram/OdontogramPage.vue'),
  },
  treatments: {
    key: 'treatments',
    label: 'Perawatan',
    icon: 'layers',
    path: 'treatments',
    permission: 'treatment.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'treatments', title: 'Perawatan' },
  },
  billing: {
    key: 'billing',
    label: 'Billing & Invoice',
    icon: 'invoice',
    path: 'billing',
    permission: 'finance.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'invoices', title: 'Billing & Invoice' },
  },
  cashier: {
    key: 'cashier',
    label: 'Kasir',
    icon: 'invoice',
    path: 'cashier',
    // Front-desk billing workflow. Cashier/receptionist roles hold
    // billing.view/create/update; finance roles keep full billing module.
    permission: 'billing.view',
    component: () => import('@/modules/cashier/CashierPage.vue'),
  },
  inventory: {
    key: 'inventory',
    label: 'Inventaris',
    icon: 'box',
    path: 'inventory',
    permission: 'inventory.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'inventory_items', title: 'Inventaris' },
  },
  pharmacy: {
    key: 'pharmacy',
    label: 'Farmasi',
    icon: 'pharmacy',
    path: 'pharmacy',
    permission: 'inventory.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'pharmacy_items', title: 'Farmasi' },
  },
  laboratory: {
    key: 'laboratory',
    label: 'Laboratorium',
    icon: 'lab',
    path: 'laboratory',
    permission: 'medical_record.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'lab_orders', title: 'Laboratorium' },
  },
  radiology: {
    key: 'radiology',
    label: 'Radiologi',
    icon: 'xray',
    path: 'radiology',
    permission: 'medical_record.view',
    component: () => import('@/modules/settings/RadiologyPage.vue'),
  },
  doctors: {
    key: 'doctors',
    label: 'Dokter',
    icon: 'doctor',
    path: 'doctors',
    // No dedicated "doctor.view" permission is seeded; gate on the org-level
    // permission so only admin-tier roles reach the staff directory.
    permission: 'organization.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'doctors', title: 'Dokter' },
  },
  employees: {
    key: 'employees',
    label: 'Karyawan',
    icon: 'employees',
    path: 'employees',
    permission: 'user.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'employees', title: 'Karyawan' },
  },
  branches: {
    key: 'branches',
    label: 'Cabang',
    icon: 'branch',
    path: 'branches',
    permission: 'branch.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'branches', title: 'Cabang' },
  },
  organization: {
    key: 'organization',
    label: 'Organisasi',
    icon: 'org',
    path: 'organization',
    permission: 'organization.view',
    component: () => import('@/modules/settings/OrganizationPage.vue'),
  },
  users: {
    key: 'users',
    label: 'Users & Roles',
    icon: 'shield',
    path: 'users',
    permission: 'user.view',
    component: () => import('@/modules/settings/UsersRolesPage.vue'),
  },
  crm: {
    key: 'crm',
    label: 'CRM',
    icon: 'crm',
    path: 'crm',
    permission: 'crm.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'crm_contacts', title: 'CRM' },
  },
  reports: {
    key: 'reports',
    label: 'Laporan',
    icon: 'chart',
    path: 'reports',
    permission: 'report.view',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'reports', title: 'Laporan' },
  },
  ai: {
    key: 'ai',
    label: 'AI Assistant',
    icon: 'ai',
    path: 'ai',
    permission: 'organization.update',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'ai_queries', title: 'AI Assistant' },
  },
  integrations: {
    key: 'integrations',
    label: 'Integrasi',
    icon: 'plug',
    path: 'integrations',
    permission: 'organization.update',
    component: () => import('@/shared/components/ModulePage.vue'),
    meta: { module: 'integration_configs', title: 'Integrasi' },
  },
  subscription: {
    key: 'subscription',
    label: 'Subscription',
    icon: 'card',
    path: 'subscription',
    permission: 'organization.update',
    component: () => import('@/modules/subscription/SubscriptionPage.vue'),
  },
  settings: {
    key: 'settings',
    label: 'Settings',
    icon: 'settings',
    path: 'settings',
    permission: 'organization.update',
    component: () => import('@/modules/settings/SettingsPage.vue'),
  },
}

export const PORTALS: Record<PortalId, PortalDef> = {
  /**
   * Admin portal — full management console with a grouped sidebar.
   * Audience: super_admin, owner, branch_manager, admin, finance, hr.
   */
  admin: {
    id: 'admin',
    label: 'Admin Console',
    tagline: 'Management Console',
    prefix: '/admin',
    roles: ['super_admin', 'owner', 'branch_manager', 'admin', 'finance', 'hr'],
    theme: {
      accent: '#4f46e5',
      accentSoft: '#eef2ff',
      sidebarBg: '#ffffff',
      sidebarText: '#4b5563',
      activeBg: '#eef2ff',
      activeText: '#4338ca',
    },
    dashboard: () => import('@/modules/dashboard/AdminDashboard.vue'),
    nav: [
      {
        title: 'Main',
        items: [
          { label: 'Dashboard', icon: 'home', module: 'dashboard' },
          { label: 'Appointment', icon: 'calendar', module: 'appointments' },
          { label: 'Pasien', icon: 'users', module: 'patients' },
          { label: 'Rekam Medis', icon: 'file', module: 'emr' },
          { label: 'Odontogram', icon: 'tooth', module: 'odontogram' },
          { label: 'Perawatan', icon: 'layers', module: 'treatments' },
        ],
      },
      {
        title: 'Operations',
        items: [
          { label: 'Billing', icon: 'invoice', module: 'billing' },
          { label: 'Inventaris', icon: 'box', module: 'inventory' },
          { label: 'Farmasi', icon: 'pharmacy', module: 'pharmacy' },
          { label: 'Laboratorium', icon: 'lab', module: 'laboratory' },
          { label: 'Radiologi', icon: 'xray', module: 'radiology' },
        ],
      },
      {
        title: 'Management',
        items: [
          { label: 'Dokter', icon: 'doctor', module: 'doctors' },
          { label: 'Karyawan', icon: 'employees', module: 'employees' },
          { label: 'Cabang', icon: 'branch', module: 'branches' },
          { label: 'Organisasi', icon: 'org', module: 'organization' },
          { label: 'Users & Roles', icon: 'shield', module: 'users' },
          { label: 'CRM', icon: 'crm', module: 'crm' },
        ],
      },
      {
        title: 'Reports & Integration',
        items: [
          { label: 'Laporan', icon: 'chart', module: 'reports' },
          { label: 'AI Assistant', icon: 'ai', module: 'ai' },
          { label: 'Integrasi', icon: 'plug', module: 'integrations' },
        ],
      },
      {
        title: 'System',
        items: [
          { label: 'Subscription', icon: 'card', module: 'subscription' },
          { label: 'Settings', icon: 'settings', module: 'settings' },
        ],
      },
    ],
  },

  /**
   * Doctor portal — slim clinical rail, care-focused.
   * Audience: doctor, dentist_specialist, nurse.
   */
  doctor: {
    id: 'doctor',
    label: 'Klinik Dokter',
    tagline: 'Clinical Workspace',
    prefix: '/doctor',
    roles: ['doctor', 'dentist_specialist', 'nurse'],
    theme: {
      accent: '#0d9488',
      accentSoft: '#f0fdfa',
      sidebarBg: '#0f766e',
      sidebarText: '#ccfbf1',
      activeBg: '#0d9488',
      activeText: '#ffffff',
    },
    dashboard: () => import('@/modules/dashboard/DoctorDashboard.vue'),
    nav: [
      {
        title: 'Praktik',
        items: [
          { label: 'Dashboard', icon: 'home', module: 'dashboard' },
          { label: 'Jadwal & Kunjungan', icon: 'calendar', module: 'appointments' },
          { label: 'Pasien', icon: 'users', module: 'patients' },
          { label: 'Rekam Medis', icon: 'file', module: 'emr' },
          { label: 'Odontogram', icon: 'tooth', module: 'odontogram' },
          { label: 'Perawatan', icon: 'layers', module: 'treatments' },
        ],
      },
    ],
  },

  /**
   * Receptionist portal — top navigation, front-desk focused.
   * Audience: receptionist, cashier, customer_service, marketing,
   * pharmacist, laboratory, inventory_staff (support roles).
   */
  receptionist: {
    id: 'receptionist',
    label: 'Front Desk',
    tagline: 'Resepsionis',
    prefix: '/receptionist',
    roles: [
      'receptionist', 'cashier', 'customer_service', 'marketing',
      'pharmacist', 'laboratory', 'inventory_staff',
    ],
    theme: {
      accent: '#2563eb',
      accentSoft: '#eff6ff',
      sidebarBg: '#ffffff',
      sidebarText: '#475569',
      activeBg: '#dbeafe',
      activeText: '#1d4ed8',
    },
    dashboard: () => import('@/modules/dashboard/ReceptionistDashboard.vue'),
    nav: [
      {
        title: 'Front Desk',
        items: [
          { label: 'Dashboard', icon: 'home', module: 'dashboard' },
          { label: 'Appointment', icon: 'calendar', module: 'appointments' },
          { label: 'Pasien', icon: 'users', module: 'patients' },
          { label: 'CRM', icon: 'crm', module: 'crm' },
          { label: 'Kasir', icon: 'invoice', module: 'cashier' },
          { label: 'Billing', icon: 'invoice', module: 'billing' },
        ],
      },
    ],
  },
}

/** Ordered list of portals (used for lookups). */
export const PORTAL_LIST: PortalDef[] = [PORTALS.admin, PORTALS.doctor, PORTALS.receptionist]

export const DEFAULT_PORTAL: PortalId = 'admin'
