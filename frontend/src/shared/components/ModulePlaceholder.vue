<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const title = computed(() => (route.meta.title as string) ?? 'Modul')
const desc = computed(() => (route.meta.desc as string) ?? 'Modul ini sedang dalam tahap pengembangan dan akan tersedia dalam waktu dekat.')
const icon = computed(() => (route.meta.icon as string) ?? 'construct')
</script>

<template>
  <div class="mod-placeholder">
    <div class="mod-placeholder-icon">
      <svg v-if="icon === 'calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <svg v-else-if="icon === 'tooth'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
      </svg>
      <svg v-else-if="icon === 'pharmacy'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <svg v-else-if="icon === 'ai'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
      </svg>
      <svg v-else-if="icon === 'construct'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      </svg>
      <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
      </svg>
    </div>
    <h1 class="mod-placeholder-title">{{ title }}</h1>
    <p class="mod-placeholder-desc">{{ desc }}</p>
    <div class="mod-placeholder-status">
      <span class="mod-pill mod-pill-amber">Dalam Pengembangan</span>
    </div>
    <div class="mod-placeholder-features">
      <p>Fitur yang akan tersedia:</p>
      <ul>
        <li>Database &amp; API backend sudah siap</li>
        <li>Endpoint {{ title }} aktif di <code>api/v1/{{ route.path.split('/').pop() }}</code></li>
        <li>Integrasi dengan modul terkait</li>
        <li>UI akan tersedia di update berikutnya</li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.mod-placeholder { max-width: 520px; margin: 2.5rem auto; text-align: center; }
.mod-placeholder-icon { width: 96px; height: 96px; margin: 0 auto 1.5rem; border-radius: 50%; background: #f0f9ff; color: #0ea5e9; display: flex; align-items: center; justify-content: center; }
.mod-placeholder-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0 0 0.75rem; }
.mod-placeholder-desc { font-size: 1rem; color: #64748b; line-height: 1.6; margin: 0 0 1.5rem; }
.mod-placeholder-status { margin-bottom: 1.75rem; }
.mod-pill { display: inline-block; padding: 0.25rem 0.875rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.mod-pill-amber { background: #fef3c7; color: #92400e; }
.mod-placeholder-features { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; text-align: left; }
.mod-placeholder-features p { font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0 0 0.75rem; }
.mod-placeholder-features ul { list-style: none; padding: 0; margin: 0; }
.mod-placeholder-features li { font-size: 0.875rem; color: #475569; padding: 0.375rem 0; padding-left: 1.5rem; position: relative; }
.mod-placeholder-features li::before { content: '✓'; position: absolute; left: 0; color: #10b981; font-weight: 700; }
.mod-placeholder-features code { background: #e5e7eb; padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.8125rem; }
</style>