<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/core/auth/useAuth'
import { authApi } from '@/modules/auth/api/authApi'
import type { AuthUser } from '@/modules/auth/api/authApi'
import logoImg from '@/assets/logo-transparant.jpg'

const route = useRoute()
const router = useRouter()
const { logout } = useAuth()
const sidebarOpen = ref(false)

const userName = ref('')
const userEmail = ref('')
const userInitial = ref('U')

/* ===== Permission-based sidebar filtering ===== */
const allPerms = [
  'dashboard', 'appointments', 'patients', 'emr', 'odontogram', 'treatment',
  'billing', 'inventory', 'pharmacy', 'laboratory', 'radiology',
  'doctors', 'employees', 'branches', 'organization', 'users', 'crm',
  'reports', 'ai', 'integrations',
  'subscription', 'settings',
]

function getUserPermsFromStorage(): string[] {
  try {
    const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}') as { email?: string }
    const userEmailStored = authUser.email
    if (!userEmailStored) return allPerms
    const usersData = JSON.parse(localStorage.getItem('users_roles_data') || '[]') as { email?: string; permissions?: string[] }[]
    const found = usersData.find(u => u.email === userEmailStored)
    const perms = found?.permissions
    if (perms && perms.length > 0) return perms
    return allPerms
  } catch {
    return allPerms
  }
}

const userPerms = ref<string[]>(getUserPermsFromStorage())

const visibleMenuGroups = computed<MenuGroup[]>(() => {
  return menuGroups
    .map(group => ({
      ...group,
      items: group.items.filter(item => item.perm === 'dashboard' || userPerms.value.includes(item.perm)),
    }))
    .filter(group => group.items.length > 0)
})

interface MenuItem { label: string; to?: string; icon: string; perm: string }
interface MenuGroup { title: string; items: MenuItem[] }

const menuGroups: MenuGroup[] = [
  {
    title: 'Main',
    items: [
      { label: 'Dashboard', to: '/dashboard', icon: 'home', perm: 'dashboard' },
      { label: 'Appointment', to: '/appointments', icon: 'calendar', perm: 'appointments' },
      { label: 'Patients', to: '/patients', icon: 'users', perm: 'patients' },
      { label: 'Medical Records', to: '/emr', icon: 'file', perm: 'emr' },
      { label: 'Odontogram', to: '/odontogram', icon: 'tooth', perm: 'odontogram' },
      { label: 'Treatment', to: '/treatments', icon: 'layers', perm: 'treatment' },
    ],
  },
  {
    title: 'Operations',
    items: [
      { label: 'Billing', to: '/billing', icon: 'invoice', perm: 'billing' },
      { label: 'Inventory', to: '/inventory', icon: 'box', perm: 'inventory' },
      { label: 'Pharmacy', to: '/pharmacy', icon: 'pharmacy', perm: 'pharmacy' },
      { label: 'Laboratory', to: '/laboratory', icon: 'lab', perm: 'laboratory' },
      { label: 'Radiology', to: '/radiology', icon: 'xray', perm: 'radiology' },
    ],
  },
  {
    title: 'Management',
    items: [
      { label: 'Doctors', to: '/doctors', icon: 'doctor', perm: 'doctors' },
      { label: 'Employees', to: '/employees', icon: 'employees', perm: 'employees' },
      { label: 'Branches', to: '/branches', icon: 'branch', perm: 'branches' },
      { label: 'Organization', to: '/organization', icon: 'org', perm: 'organization' },
      { label: 'Users & Roles', to: '/users', icon: 'shield', perm: 'users' },
      { label: 'CRM', to: '/crm', icon: 'crm', perm: 'crm' },
    ],
  },
  {
    title: 'Reports & Integration',
    items: [
      { label: 'Reports', to: '/reports', icon: 'chart', perm: 'reports' },
      { label: 'AI Assistant', to: '/ai', icon: 'ai', perm: 'ai' },
      { label: 'Integrations', to: '/integrations', icon: 'plug', perm: 'integrations' },
    ],
  },
  {
    title: 'System',
    items: [
      { label: 'Subscription', to: '/subscription', icon: 'card', perm: 'subscription' },
      { label: 'Settings', to: '/settings', icon: 'settings', perm: 'settings' },
    ],
  },
]

function isActive(to?: string): boolean {
  return to ? route.path === to || (to !== '/dashboard' && route.path.startsWith(to)) : false
}

async function loadProfile(): Promise<void> {
  try {
    const profile = await authApi.profile()
    userName.value = profile.name
    userEmail.value = profile.email
    userInitial.value = profile.name?.charAt(0)?.toUpperCase() ?? 'U'
    localStorage.setItem('auth_user', JSON.stringify(profile))
    userPerms.value = getUserPermsFromStorage()
  } catch {
    try {
      const u = JSON.parse(localStorage.getItem('auth_user') || '{}') as AuthUser
      userName.value = u.name ?? ''
      userEmail.value = u.email ?? ''
      userInitial.value = (u.name ?? 'U').charAt(0).toUpperCase()
      userPerms.value = getUserPermsFromStorage()
    } catch { /* ignore */ }
  }
}

onMounted(() => { loadProfile() })

function go(path: string): void { sidebarOpen.value = false; router.push(path) }
async function handleLogout(): Promise<void> { localStorage.removeItem('auth_user'); await logout() }

const pageTitle = computed(() => (route.meta.title as string) ?? 'Dashboard')
</script>

<template>
  <div class="app-shell">
    <transition name="fade">
      <div v-if="sidebarOpen" class="app-backdrop" @click="sidebarOpen = false"></div>
    </transition>

    <aside class="app-sidebar" :class="{ open: sidebarOpen }">
      <div class="app-sidebar-head">
        <button class="app-brand" @click="go('/dashboard')">
          <span class="app-brand-mark">
            <img :src="logoImg" alt="My Dent Care" class="app-brand-logo" />
          </span>
          <span class="app-brand-text">
            <span class="app-brand-name">My Dent Care</span>
            <span class="app-brand-sub">Dental ERP</span>
          </span>
        </button>
        <button class="app-sidebar-close" aria-label="Tutup" @click="sidebarOpen = false">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="app-nav">
        <div v-for="group in visibleMenuGroups" :key="group.title" class="app-nav-group">
          <p class="app-nav-title">{{ group.title }}</p>
          <button
            v-for="item in group.items"
            :key="item.label"
            type="button"
            class="app-nav-item"
            :class="{ active: isActive(item.to) }"
            @click="item.to && go(item.to)"
          >
            <span class="app-nav-icon">
              <svg v-if="item.icon === 'home'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" /></svg>
              <svg v-else-if="item.icon === 'calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              <svg v-else-if="item.icon === 'users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              <svg v-else-if="item.icon === 'file'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              <svg v-else-if="item.icon === 'tooth'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
              <svg v-else-if="item.icon === 'layers'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4" /></svg>
              <svg v-else-if="item.icon === 'invoice'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
              <svg v-else-if="item.icon === 'box'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
              <svg v-else-if="item.icon === 'pharmacy'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
              <svg v-else-if="item.icon === 'lab'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
              <svg v-else-if="item.icon === 'xray'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 3L3 6l18 18M3 6v12a2 2 0 002 2h14a2 2 0 002-2V6" /></svg>
              <svg v-else-if="item.icon === 'doctor'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              <svg v-else-if="item.icon === 'employees'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              <svg v-else-if="item.icon === 'branch'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
              <svg v-else-if="item.icon === 'org'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M10 10h.01M14 10h.01M10 14h.01M14 14h.01" /></svg>
              <svg v-else-if="item.icon === 'shield'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
              <svg v-else-if="item.icon === 'crm'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
              <svg v-else-if="item.icon === 'chart'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
              <svg v-else-if="item.icon === 'ai'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
              <svg v-else-if="item.icon === 'plug'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              <svg v-else-if="item.icon === 'card'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h3m-6 5h14a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
            </span>
            <span class="app-nav-label">{{ item.label }}</span>
          </button>
        </div>
      </nav>

      <div class="app-sidebar-foot">
        <button class="app-nav-item" type="button" @click="handleLogout">
          <span class="app-nav-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          </span>
          <span class="app-nav-label">Logout</span>
        </button>
      </div>
    </aside>

    <div class="app-main">
      <header class="app-topbar">
        <button class="app-hamburger" aria-label="Buka menu" @click="sidebarOpen = true">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <div class="app-topbar-left">
          <span class="app-topbar-page">{{ pageTitle }}</span>
        </div>
        <div class="app-topbar-right">
          <div class="app-user">
            <span class="app-user-avatar">{{ userInitial }}</span>
            <span class="app-user-name">{{ userName || 'User' }}</span>
          </div>
        </div>
      </header>

      <main class="app-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: #f5f5f5;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* ===== SIDEBAR (Mantis style) ===== */
.app-sidebar {
  width: 260px;
  flex-shrink: 0;
  background: #ffffff;
  color: #595959;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 50;
  border-right: 1px solid #f0f0f0;
}
.app-sidebar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.25rem 0.875rem;
  border-bottom: 1px solid #f0f0f0;
}
.app-brand {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  font-family: inherit;
}
.app-brand-mark {
  width: 40px; height: 40px;
  border-radius: 8px;
  background: #e6f7ff;
  border: 1px solid #bae7ff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 3px;
}
.app-brand-logo { height: 28px; width: auto; object-fit: contain; display: block; }
.app-brand-text { display: flex; flex-direction: column; line-height: 1.2; }
.app-brand-name {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1890ff;
}
.app-brand-sub {
  font-size: 0.6875rem;
  color: #bfbfbf;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.app-sidebar-close { display: none; background: none; border: none; color: #bfbfbf; cursor: pointer; }

.app-nav { flex: 1; padding: 1rem 0.75rem; overflow-y: auto; }
.app-nav-group { margin-bottom: 1.25rem; }
.app-nav-title {
  font-size: 0.6875rem;
  font-weight: 700;
  color: #bfbfbf;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin: 0 0 0.5rem;
  padding: 0 0.625rem;
}
.app-nav-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  width: 100%;
  padding: 0.5625rem 0.75rem;
  margin-bottom: 2px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #595959;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  text-align: left;
  transition: all 0.2s;
}
.app-nav-icon { display: flex; align-items: center; justify-content: center; width: 20px; flex-shrink: 0; }
.app-nav-item:hover { background: #f5f5f5; color: #262626; }
.app-nav-item.active {
  background: #e6f7ff;
  color: #1890ff;
}
.app-nav-item.active .app-nav-icon { color: #1890ff; }
.app-sidebar-foot {
  padding: 0.75rem;
  border-top: 1px solid #f0f0f0;
}

/* ===== MAIN ===== */
.app-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.app-topbar {
  height: 60px;
  background: #ffffff;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0 1.5rem;
  position: sticky;
  top: 0;
  z-index: 40;
}
.app-hamburger { display: none; background: none; border: none; color: #595959; cursor: pointer; padding: 4px; }
.app-topbar-left { flex: 1; }
.app-topbar-page { font-size: 1.125rem; font-weight: 700; color: #262626; }
.app-topbar-right { margin-left: auto; }
.app-user { display: flex; align-items: center; gap: 0.625rem; }
.app-user-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: #1890ff; color: #fff;
  font-weight: 700; font-size: 0.8125rem;
  display: flex; align-items: center; justify-content: center;
}
.app-user-name { font-size: 0.875rem; font-weight: 600; color: #262626; }

.app-content { flex: 1; padding: 1.5rem; }

.app-backdrop { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 45; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
  .app-sidebar { position: fixed; left: 0; top: 0; height: 100vh; transform: translateX(-100%); transition: transform 0.25s ease; }
  .app-sidebar.open { transform: translateX(0); box-shadow: 0 0 40px rgba(0,0,0,0.15); }
  .app-sidebar-close { display: block; }
  .app-hamburger { display: block; }
  .app-topbar { padding: 0 1rem; }
  .app-content { padding: 1rem; }
}
</style>