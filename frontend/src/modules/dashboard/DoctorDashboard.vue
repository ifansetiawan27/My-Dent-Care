<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import { usePortal } from '@/core/portal/usePortal'
import { todayKey, dateKey, fmtTime } from '@/shared/utils/datetime'

const router = useRouter()
const { modulePath } = usePortal()

const loading = ref(true)
const error = ref<string | null>(null)
const appointments = ref<any[]>([])

const today = todayKey()

const todays = computed(() =>
  appointments.value
    .filter((a) => dateKey(a.scheduled_at) === today)
    .sort((a, b) => String(a.scheduled_at ?? '').localeCompare(String(b.scheduled_at ?? '')))
)

const upcoming = computed(() =>
  appointments.value
    .filter((a) => dateKey(a.scheduled_at) > today)
    .sort((a, b) => String(a.scheduled_at ?? '').localeCompare(String(b.scheduled_at ?? '')))
    .slice(0, 5)
)

async function load(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get<ApiResponse<any[]>>('/v1/appointments', { params: { per_page: 100 } })
    appointments.value = data?.data ?? []
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat jadwal.'
  } finally {
    loading.value = false
  }
}

function go(module: string): void {
  router.push(modulePath(module))
}

onMounted(() => { load() })
</script>

<template>
  <div class="dd-dash">
    <div class="dd-hero">
      <div>
        <p class="dd-eyebrow">Hari ini · {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
        <h2>{{ todays.length }} Kunjungan Tertjadwal</h2>
        <p class="dd-sub">Fokus pada perawatan — sisanya kami yang urus.</p>
      </div>
      <div class="dd-hero-actions">
        <button class="dd-btn" type="button" @click="go('emr')">Buka Rekam Medis</button>
        <button class="dd-btn ghost" type="button" @click="load" :disabled="loading">
          {{ loading ? 'Memuat…' : 'Perbarui Jadwal' }}
        </button>
      </div>
    </div>

    <p v-if="error" class="dd-error">⚠ {{ error }}</p>

    <div class="dd-grid">
      <section class="dd-card dd-schedule">
        <div class="dd-card-head">
          <h3>Antrian Kunjungan Hari Ini</h3>
          <span class="dd-count">{{ todays.length }}</span>
        </div>
        <div v-if="todays.length === 0 && !loading" class="dd-empty">
          Tidak ada kunjungan hari ini. Nikmati secangkir kopi ☕
        </div>
        <ul v-else class="dd-queue">
          <li v-for="(a, i) in todays" :key="a.id" class="dd-queue-item" @click="go('emr')">
            <span class="dd-queue-no">{{ i + 1 }}</span>
            <span class="dd-queue-time">{{ fmtTime(a.scheduled_at) }}</span>
            <span class="dd-queue-name">{{ a.patient?.full_name ?? a.patient ?? 'Pasien' }}</span>
            <span class="dd-queue-type">{{ a.type ?? '-' }}</span>
            <span class="dd-chip" :class="{ done: a.status === 'completed' }">{{ a.status ?? 'scheduled' }}</span>
          </li>
        </ul>
      </section>

      <section class="dd-card dd-upcoming">
        <div class="dd-card-head">
          <h3>Janji Mendatang</h3>
        </div>
        <div v-if="upcoming.length === 0 && !loading" class="dd-empty">Belum ada janji mendatang.</div>
        <ul v-else class="dd-queue">
          <li v-for="a in upcoming" :key="a.id" class="dd-queue-item" @click="go('appointments')">
            <span class="dd-queue-time">{{ fmtTime(a.scheduled_at) }}</span>
            <span class="dd-queue-name">{{ a.patient?.full_name ?? a.patient ?? 'Pasien' }}</span>
            <span class="dd-queue-type">{{ dateKey(a.scheduled_at) }}</span>
          </li>
        </ul>
      </section>
    </div>

    <div class="dd-shortcuts">
      <button class="dd-shortcut" type="button" @click="go('odontogram')">
        <span class="dd-shortcut-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
        Odontogram
      </button>
      <button class="dd-shortcut" type="button" @click="go('patients')">
        <span class="dd-shortcut-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
        Data Pasien
      </button>
      <button class="dd-shortcut" type="button" @click="go('treatments')">
        <span class="dd-shortcut-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4" /></svg></span>
        Perawatan
      </button>
    </div>
  </div>
</template>

<style scoped>
.dd-dash { color: #134e4a; }
.dd-hero {
  display: flex; align-items: center; justify-content: space-between; gap: 1.25rem;
  flex-wrap: wrap;
  background: linear-gradient(120deg, #0f766e, #0d9488);
  color: #fff; border-radius: 18px; padding: 1.4rem 1.6rem; margin-bottom: 1.4rem;
}
.dd-eyebrow { margin: 0 0 0.3rem; font-size: 0.76rem; font-weight: 650; letter-spacing: 0.05em; text-transform: uppercase; color: #99f6e4; }
.dd-hero h2 { margin: 0; font-size: 1.7rem; font-weight: 750; }
.dd-sub { margin: 0.3rem 0 0; font-size: 0.85rem; color: #ccfbf1; }
.dd-hero-actions { display: flex; gap: 0.55rem; flex-wrap: wrap; }
.dd-btn {
  padding: 0.55rem 1rem; border-radius: 10px; border: none; cursor: pointer;
  background: #fff; color: #0f766e; font-size: 0.84rem; font-weight: 700;
}
.dd-btn:hover { filter: brightness(0.97); }
.dd-btn.ghost { background: rgba(255,255,255,0.16); color: #fff; border: 1px solid rgba(255,255,255,0.4); }
.dd-btn:disabled { opacity: 0.6; cursor: progress; }

.dd-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; padding: 0.7rem 0.9rem; border-radius: 10px; font-size: 0.85rem; margin-bottom: 1rem; }

.dd-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.1rem; margin-bottom: 1.2rem; }
.dd-card { background: #fff; border: 1px solid #d9e6e4; border-radius: 16px; padding: 1.1rem 1.2rem; }
.dd-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.8rem; }
.dd-card-head h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.dd-count { background: #f0fdfa; color: #0f766e; border: 1px solid #b7e8e1; border-radius: 999px; padding: 0.1rem 0.6rem; font-size: 0.74rem; font-weight: 700; }
.dd-empty { color: #5f8b86; font-size: 0.85rem; padding: 0.9rem 0; }

.dd-queue { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; }
.dd-queue-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.6rem 0.4rem; border-radius: 10px; cursor: pointer;
  border-bottom: 1px solid #f1f5f4;
}
.dd-queue-item:hover { background: #f0fdfa; }
.dd-queue-item:last-child { border-bottom: none; }
.dd-queue-no { width: 24px; height: 24px; border-radius: 50%; background: #0f766e; color: #fff; display: grid; place-items: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0; }
.dd-queue-time { font-weight: 700; font-size: 0.82rem; color: #0f766e; min-width: 52px; }
.dd-queue-name { flex: 1; font-weight: 600; font-size: 0.86rem; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dd-queue-type { font-size: 0.72rem; color: #64748b; }
.dd-chip { padding: 0.12rem 0.5rem; border-radius: 999px; background: #f0fdfa; color: #0f766e; font-size: 0.68rem; font-weight: 700; border: 1px solid #b7e8e1; }
.dd-chip.done { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }

.dd-shortcuts { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.8rem; }
.dd-shortcut {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.85rem 1rem; border-radius: 14px;
  border: 1px solid #d9e6e4; background: #fff; color: #134e4a;
  font-size: 0.86rem; font-weight: 650; cursor: pointer;
  transition: transform 0.13s ease, box-shadow 0.13s ease;
}
.dd-shortcut:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(15, 118, 110, 0.12); }
.dd-shortcut-ico { width: 36px; height: 36px; border-radius: 10px; background: #f0fdfa; color: #0f766e; display: grid; place-items: center; }

@media (max-width: 900px) {
  .dd-grid { grid-template-columns: 1fr; }
}
</style>
