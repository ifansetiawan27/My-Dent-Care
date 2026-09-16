<script setup lang="ts">
import { ref, computed } from 'vue'
import logoImg from '@/assets/logo-transparant.jpg'
import PortalIcon from './PortalIcon.vue'
import { usePortal } from '@/core/portal/usePortal'
import { usePortalChrome } from '@/core/portal/usePortalChrome'

const { visibleNav, portal } = usePortal()
const { userName, userInitial, isActive, pageTitle, handleLogout, go } = usePortalChrome()

const railOpen = ref(false)

// The doctor portal flattens its single nav group into a rail list.
const railItems = computed(() => visibleNav.value.flatMap((g) => g.items))
</script>

<template>
  <div class="doc-shell">
    <div class="doc-rail" :class="{ open: railOpen }">
      <div class="doc-rail-head">
        <button class="doc-brand" @click="go(`${portal.prefix}/dashboard`)">
          <span class="doc-brand-mark"><img :src="logoImg" alt="My Dent Care" /></span>
          <span class="doc-brand-text">
            <span class="doc-brand-name">My Dent Care</span>
            <span class="doc-brand-sub">{{ portal.tagline }}</span>
          </span>
        </button>
        <button class="doc-close" aria-label="Tutup" @click="railOpen = false">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="doc-nav">
        <button
          v-for="item in railItems"
          :key="item.module"
          type="button"
          class="doc-nav-item"
          :class="{ active: isActive(item.module) }"
          @click="go(`${portal.prefix}/${item.module}`); railOpen = false"
        >
          <span class="doc-nav-icon"><PortalIcon :name="item.icon" /></span>
          <span class="doc-nav-label">{{ item.label }}</span>
        </button>
      </nav>

      <div class="doc-rail-foot">
        <div class="doc-user">
          <span class="doc-user-avatar">{{ userInitial }}</span>
          <span class="doc-user-meta">
            <span class="doc-user-name">{{ userName || 'Dokter' }}</span>
            <span class="doc-user-role">Dokter Gigi</span>
          </span>
        </div>
        <button class="doc-logout" type="button" @click="handleLogout">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          <span>Keluar</span>
        </button>
      </div>
    </div>

    <div class="doc-main">
      <header class="doc-topbar">
        <button class="doc-hamburger" aria-label="Buka menu" @click="railOpen = true">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <div class="doc-topbar-title">
          <h1>{{ pageTitle }}</h1>
          <p>Selamat datang, {{ userName || 'Dokter' }} — semoga harinya menyenangkan.</p>
        </div>
        <span class="doc-portal-badge">{{ portal.label }}</span>
      </header>

      <main class="doc-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.doc-shell {
  display: flex;
  min-height: 100vh;
  background: #f0fdfa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* ===== Slim clinical rail (dark teal) ===== */
.doc-rail {
  width: 236px;
  flex-shrink: 0;
  background: linear-gradient(180deg, #0f766e 0%, #115e59 100%);
  color: #ccfbf1;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 50;
}
.doc-rail-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1rem 0.9rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.14);
}
.doc-brand { display: flex; align-items: center; gap: 0.6rem; background: none; border: none; cursor: pointer; padding: 0; }
.doc-brand-mark {
  width: 38px; height: 38px; border-radius: 12px;
  background: rgba(255, 255, 255, 0.16);
  display: grid; place-items: center; overflow: hidden;
}
.doc-brand-mark img { width: 28px; height: 28px; object-fit: contain; }
.doc-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
.doc-brand-name { font-weight: 750; font-size: 0.98rem; color: #ffffff; }
.doc-brand-sub { font-size: 0.66rem; color: #99f6e4; letter-spacing: 0.07em; text-transform: uppercase; }
.doc-close { display: none; background: none; border: none; cursor: pointer; color: #ccfbf1; }

.doc-nav { flex: 1; padding: 0.85rem 0.7rem; display: flex; flex-direction: column; gap: 0.25rem; }
.doc-nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.6rem 0.75rem;
  border-radius: 11px;
  border: none;
  background: transparent;
  color: #ccfbf1;
  font-size: 0.9rem;
  font-weight: 550;
  cursor: pointer;
  text-align: left;
  transition: background 0.14s ease, color 0.14s ease;
}
.doc-nav-item:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
.doc-nav-item.active {
  background: #ffffff;
  color: #0f766e;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.16);
}
.doc-nav-icon { display: grid; place-items: center; width: 20px; flex-shrink: 0; }

.doc-rail-foot { padding: 0.9rem 0.85rem 1rem; border-top: 1px solid rgba(255, 255, 255, 0.14); display: flex; flex-direction: column; gap: 0.7rem; }
.doc-user { display: flex; align-items: center; gap: 0.6rem; }
.doc-user-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  display: grid; place-items: center;
  background: #ffffff; color: #0f766e;
  font-weight: 750; font-size: 0.85rem;
}
.doc-user-meta { display: flex; flex-direction: column; line-height: 1.2; }
.doc-user-name { font-size: 0.85rem; font-weight: 650; color: #ffffff; }
.doc-user-role { font-size: 0.7rem; color: #99f6e4; }
.doc-logout {
  display: flex; align-items: center; justify-content: center; gap: 0.45rem;
  padding: 0.5rem; border-radius: 9px;
  border: 1px solid rgba(255, 255, 255, 0.28);
  background: rgba(255, 255, 255, 0.08);
  color: #ccfbf1; font-size: 0.8rem; font-weight: 600; cursor: pointer;
}
.doc-logout:hover { background: rgba(255, 255, 255, 0.18); color: #fff; }

.doc-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.doc-topbar {
  position: sticky; top: 0; z-index: 40;
  display: flex; align-items: center; gap: 1rem;
  padding: 1rem 1.75rem;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid #d9e6e4;
}
.doc-hamburger { display: none; background: none; border: none; cursor: pointer; color: #134e4a; }
.doc-topbar-title { flex: 1; min-width: 0; }
.doc-topbar-title h1 { margin: 0; font-size: 1.2rem; font-weight: 750; color: #134e4a; }
.doc-topbar-title p { margin: 0.1rem 0 0; font-size: 0.8rem; color: #5f8b86; }
.doc-portal-badge {
  padding: 0.3rem 0.7rem; border-radius: 999px;
  background: #f0fdfa; color: #0f766e;
  font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em;
  border: 1px solid #b7e8e1;
}

.doc-content { flex: 1; padding: 1.5rem 1.75rem; }

@media (max-width: 900px) {
  .doc-rail {
    position: fixed; left: 0; top: 0;
    transform: translateX(-100%);
    transition: transform 0.22s ease;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.22);
  }
  .doc-rail.open { transform: translateX(0); }
  .doc-close { display: grid; place-items: center; }
  .doc-hamburger { display: block; }
  .doc-content { padding: 1rem; }
}
</style>
