<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import logoImg from '@/assets/logo-transparant.jpg'
import PortalIcon from './PortalIcon.vue'
import { usePortal } from '@/core/portal/usePortal'
import { usePortalChrome } from '@/core/portal/usePortalChrome'

const router = useRouter()
const { visibleNav, portal } = usePortal()
const { userName, userInitial, isActive, pageTitle, handleLogout, go } = usePortalChrome()

const menuOpen = ref(false)

// Front desk uses a single flat group rendered as horizontal tabs.
const tabs = computed(() => visibleNav.value.flatMap((g) => g.items))

async function quickPatient(): Promise<void> {
  await router.push(`${portal.value.prefix}/patients`)
}
async function quickAppointment(): Promise<void> {
  await router.push(`${portal.value.prefix}/appointments`)
}
</script>

<template>
  <div class="rec-shell">
    <header class="rec-topbar">
      <div class="rec-topbar-inner">
        <button class="rec-brand" @click="go(`${portal.prefix}/dashboard`)">
          <span class="rec-brand-mark"><img :src="logoImg" alt="My Dent Care" /></span>
          <span class="rec-brand-text">
            <span class="rec-brand-name">My Dent Care</span>
            <span class="rec-brand-sub">{{ portal.tagline }}</span>
          </span>
        </button>

        <nav class="rec-nav">
          <button
            v-for="item in tabs"
            :key="item.module"
            type="button"
            class="rec-tab"
            :class="{ active: isActive(item.module) }"
            @click="go(`${portal.prefix}/${item.module}`)"
          >
            <PortalIcon :name="item.icon" :size="17" />
            <span>{{ item.label }}</span>
          </button>
        </nav>

        <div class="rec-actions">
          <button class="rec-quick rec-quick-outline" type="button" @click="quickPatient">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Pasien Baru</span>
          </button>
          <button class="rec-quick" type="button" @click="quickAppointment">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span>Buat Janji</span>
          </button>

          <div class="rec-user-menu">
            <button class="rec-user" type="button" @click="menuOpen = !menuOpen">
              <span class="rec-user-avatar">{{ userInitial }}</span>
              <span class="rec-user-name">{{ userName || 'User' }}</span>
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div v-if="menuOpen" class="rec-user-popover">
              <button class="rec-user-pop-item" type="button" @click="menuOpen = false; handleLogout()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>Logout</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="rec-page-head">
      <div class="rec-page-head-inner">
        <h1>{{ pageTitle }}</h1>
        <p>{{ portal.label }} &middot; selamat datang, {{ userName || 'User' }}</p>
      </div>
    </div>

    <main class="rec-content">
      <div class="rec-content-inner">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped>
.rec-shell {
  min-height: 100vh;
  background: #f1f5f9;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* ===== Top navigation bar ===== */
.rec-topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
}
.rec-topbar-inner {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  max-width: 1320px;
  margin: 0 auto;
  padding: 0.65rem 1.5rem;
}
.rec-brand { display: flex; align-items: center; gap: 0.6rem; background: none; border: none; cursor: pointer; padding: 0; flex-shrink: 0; }
.rec-brand-mark {
  width: 38px; height: 38px; border-radius: 11px;
  background: v-bind('portal.theme.accentSoft');
  display: grid; place-items: center; overflow: hidden;
}
.rec-brand-mark img { width: 28px; height: 28px; object-fit: contain; }
.rec-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
.rec-brand-name { font-weight: 750; font-size: 0.98rem; color: #0f172a; }
.rec-brand-sub { font-size: 0.66rem; color: #94a3b8; letter-spacing: 0.07em; text-transform: uppercase; }

.rec-nav { display: flex; align-items: center; gap: 0.2rem; flex: 1; min-width: 0; overflow-x: auto; }
.rec-tab {
  display: inline-flex; align-items: center; gap: 0.4rem;
  padding: 0.5rem 0.8rem; border-radius: 9px;
  border: none; background: none; cursor: pointer;
  color: #475569; font-size: 0.84rem; font-weight: 600;
  white-space: nowrap;
  transition: background 0.13s ease, color 0.13s ease;
}
.rec-tab:hover { background: #f1f5f9; color: #0f172a; }
.rec-tab.active { background: v-bind('portal.theme.accentSoft'); color: v-bind('portal.theme.activeText'); }

.rec-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.rec-quick {
  display: inline-flex; align-items: center; gap: 0.4rem;
  padding: 0.5rem 0.9rem; border-radius: 9px;
  border: none; cursor: pointer;
  background: v-bind('portal.theme.accent'); color: #ffffff;
  font-size: 0.82rem; font-weight: 650;
  transition: filter 0.13s ease;
}
.rec-quick:hover { filter: brightness(1.08); }
.rec-quick-outline {
  background: #ffffff; color: v-bind('portal.theme.activeText');
  border: 1px solid v-bind('portal.theme.accent');
}
.rec-quick-outline:hover { background: v-bind('portal.theme.accentSoft'); }

.rec-user-menu { position: relative; }
.rec-user {
  display: flex; align-items: center; gap: 0.45rem;
  padding: 0.35rem 0.55rem; border-radius: 9px;
  border: none; background: none; cursor: pointer;
}
.rec-user:hover { background: #f1f5f9; }
.rec-user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  display: grid; place-items: center;
  background: v-bind('portal.theme.accent'); color: #fff;
  font-weight: 700; font-size: 0.78rem;
}
.rec-user-name { font-size: 0.82rem; font-weight: 650; color: #334155; }
.rec-user-popover {
  position: absolute; right: 0; top: calc(100% + 0.4rem);
  background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
  padding: 0.3rem; min-width: 160px;
}
.rec-user-pop-item {
  display: flex; align-items: center; gap: 0.5rem;
  width: 100%; padding: 0.5rem 0.6rem; border-radius: 7px;
  border: none; background: none; cursor: pointer;
  color: #dc2626; font-size: 0.82rem; font-weight: 600; text-align: left;
}
.rec-user-pop-item:hover { background: #fef2f2; }

.rec-page-head { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
.rec-page-head-inner { max-width: 1320px; margin: 0 auto; padding: 1.1rem 1.5rem 1.2rem; }
.rec-page-head h1 { margin: 0; font-size: 1.35rem; font-weight: 750; color: #0f172a; }
.rec-page-head p { margin: 0.15rem 0 0; font-size: 0.82rem; color: #64748b; }

.rec-content { padding: 1.5rem 0 2.5rem; }
.rec-content-inner { max-width: 1320px; margin: 0 auto; padding: 0 1.5rem; }

@media (max-width: 900px) {
  .rec-topbar-inner { flex-wrap: wrap; gap: 0.6rem; padding: 0.55rem 1rem; }
  .rec-nav { order: 3; width: 100%; }
  .rec-user-name { display: none; }
  .rec-page-head-inner { padding: 0.85rem 1rem 1rem; }
  .rec-content-inner { padding: 0 1rem; }
}
</style>
