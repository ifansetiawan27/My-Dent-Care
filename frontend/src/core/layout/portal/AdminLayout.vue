<script setup lang="ts">
import { ref } from 'vue'
import logoImg from '@/assets/logo-transparant.jpg'
import PortalIcon from './PortalIcon.vue'
import { usePortal } from '@/core/portal/usePortal'
import { usePortalChrome } from '@/core/portal/usePortalChrome'

const { visibleNav, portal } = usePortal()
const { userName, userInitial, isActive, pageTitle, handleLogout, go } = usePortalChrome()

const sidebarOpen = ref(false)
</script>

<template>
  <div class="admin-shell">
    <transition name="fade">
      <div v-if="sidebarOpen" class="admin-backdrop" @click="sidebarOpen = false"></div>
    </transition>

    <aside class="admin-sidebar" :class="{ open: sidebarOpen }">
      <div class="admin-head">
        <button class="admin-brand" @click="go(`${portal.prefix}/dashboard`)">
          <span class="admin-brand-mark"><img :src="logoImg" alt="My Dent Care" /></span>
          <span class="admin-brand-text">
            <span class="admin-brand-name">My Dent Care</span>
            <span class="admin-brand-sub">{{ portal.tagline }}</span>
          </span>
        </button>
        <button class="admin-close" aria-label="Tutup" @click="sidebarOpen = false">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="admin-nav">
        <div v-for="group in visibleNav" :key="group.title" class="admin-nav-group">
          <p class="admin-nav-title">{{ group.title }}</p>
          <button
            v-for="item in group.items"
            :key="item.module"
            type="button"
            class="admin-nav-item"
            :class="{ active: isActive(item.module) }"
            @click="go(`${portal.prefix}/${item.module}`)"
          >
            <span class="admin-nav-icon"><PortalIcon :name="item.icon" /></span>
            <span class="admin-nav-label">{{ item.label }}</span>
          </button>
        </div>
      </nav>

      <div class="admin-foot">
        <button class="admin-nav-item" type="button" @click="handleLogout">
          <span class="admin-nav-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          </span>
          <span class="admin-nav-label">Logout</span>
        </button>
      </div>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <button class="admin-hamburger" aria-label="Buka menu" @click="sidebarOpen = true">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <div class="admin-topbar-left">
          <span class="admin-crumb">{{ portal.label }}</span>
          <span class="admin-topbar-page">{{ pageTitle }}</span>
        </div>
        <div class="admin-topbar-right">
          <div class="admin-user">
            <span class="admin-user-avatar">{{ userInitial }}</span>
            <span class="admin-user-name">{{ userName || 'User' }}</span>
          </div>
        </div>
      </header>

      <main class="admin-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.admin-shell {
  display: flex;
  min-height: 100vh;
  background: #f4f5f7;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* ===== Grouped sidebar ===== */
.admin-sidebar {
  width: 262px;
  flex-shrink: 0;
  background: #ffffff;
  color: #4b5563;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 50;
  border-right: 1px solid #e5e7eb;
}
.admin-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.15rem 1.15rem 0.85rem;
  border-bottom: 1px solid #e5e7eb;
}
.admin-brand {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}
.admin-brand-mark {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: v-bind('portal.theme.accentSoft');
  display: grid;
  place-items: center;
  overflow: hidden;
}
.admin-brand-mark img { width: 26px; height: 26px; object-fit: contain; }
.admin-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
.admin-brand-name { font-weight: 700; font-size: 0.98rem; color: #111827; }
.admin-brand-sub { font-size: 0.7rem; color: #9ca3af; letter-spacing: 0.04em; text-transform: uppercase; }
.admin-close { display: none; background: none; border: none; cursor: pointer; color: #6b7280; }

.admin-nav { flex: 1; padding: 0.85rem 0.75rem 1rem; }
.admin-nav-group + .admin-nav-group { margin-top: 1.1rem; }
.admin-nav-title {
  font-size: 0.66rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: #9ca3af;
  padding: 0 0.6rem 0.4rem;
  margin: 0;
}
.admin-nav-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  width: 100%;
  padding: 0.55rem 0.65rem;
  border-radius: 9px;
  border: none;
  background: none;
  color: #4b5563;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  text-align: left;
  transition: background 0.13s ease, color 0.13s ease;
}
.admin-nav-item:hover { background: #f3f4f6; color: #111827; }
.admin-nav-item.active {
  background: v-bind('portal.theme.accentSoft');
  color: v-bind('portal.theme.activeText');
  font-weight: 650;
}
.admin-nav-icon { display: grid; place-items: center; width: 20px; flex-shrink: 0; }
.admin-foot { padding: 0.75rem; border-top: 1px solid #e5e7eb; }

.admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.admin-topbar {
  position: sticky;
  top: 0;
  z-index: 40;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.85rem 1.5rem;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid #e5e7eb;
}
.admin-hamburger { display: none; background: none; border: none; cursor: pointer; color: #374151; }
.admin-topbar-left { display: flex; flex-direction: column; line-height: 1.2; }
.admin-crumb { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: v-bind('portal.theme.accent'); }
.admin-topbar-page { font-size: 1.05rem; font-weight: 700; color: #111827; }
.admin-user { display: flex; align-items: center; gap: 0.55rem; }
.admin-user-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  display: grid; place-items: center;
  background: v-bind('portal.theme.accent'); color: #fff;
  font-weight: 700; font-size: 0.82rem;
}
.admin-user-name { font-size: 0.85rem; font-weight: 600; color: #374151; }

.admin-content { flex: 1; padding: 1.5rem; }

.admin-backdrop { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.45); z-index: 60; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 900px) {
  .admin-sidebar {
    position: fixed;
    left: 0; top: 0;
    transform: translateX(-100%);
    transition: transform 0.22s ease;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
  }
  .admin-sidebar.open { transform: translateX(0); }
  .admin-close { display: grid; place-items: center; }
  .admin-hamburger { display: block; }
  .admin-content { padding: 1rem; }
}
</style>
