<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import { usePortal } from '@/core/portal/usePortal'
import { todayKey, dateKey } from '@/shared/utils/datetime'

const router = useRouter()
const { modulePath } = usePortal()

interface Stat {
  label: string
  value: string
  hint: string
  icon: string
  to: string
}

const loading = ref(true)
const error = ref<string | null>(null)
const stats = ref<Stat[]>([])
const recent = ref<any[]>([])

async function load(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const [apptRes, patientRes] = await Promise.all([
      api.get<ApiResponse<any[]>>('/v1/appointments', { params: { per_page: 100 } }),
      api.get<ApiResponse<any[]>>('/v1/patients', { params: { per_page: 100 } }),
    ])

    const appts = apptRes.data?.data ?? []
    const patients = patientRes.data?.data ?? []
    const today = todayKey()
    const todays = appts.filter((a) => dateKey(a.scheduled_at) === today)

    stats.value = [
      { label: 'Total Pasien', value: String(patients.length), hint: 'terdaftar di klinik', icon: 'users', to: 'patients' },
      { label: 'Appointment Hari Ini', value: String(todays.length), hint: 'jadwal aktif', icon: 'calendar', to: 'appointments' },
      { label: 'Total Appointment', value: String(appts.length), hint: 'seluruh periode', icon: 'file', to: 'appointments' },
      { label: 'Cabang Aktif', value: '—', hint: 'lihat daftar cabang', icon: 'branch', to: 'branches' },
    ]

    recent.value = appts
      .slice()
      .sort((a, b) => String(b.scheduled_at ?? '').localeCompare(String(a.scheduled_at ?? '')))
      .slice(0, 6)
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat ringkasan.'
  } finally {
    loading.value = false
  }
}

function go(module: string): void {
  router.push(modulePath(module))
}

function fmtDate(iso: string | undefined): string {
  if (!iso) return '-'
  const d = new Date(iso)
  return Number.isNaN(d.getTime()) ? iso : d.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => { load() })
</script>

<template>
  <div class="ad-dash">
    <div class="ad-head">
      <div>
        <h2>Ringkasan Operasional</h2>
        <p>Pantau seluruh aktivitas klinik dari satu layar.</p>
      </div>
      <button class="ad-refresh" type="button" :disabled="loading" @click="load">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        <span>{{ loading ? 'Memuat…' : 'Refresh' }}</span>
      </button>
    </div>

    <p v-if="error" class="ad-error">⚠ {{ error }}</p>

    <div class="ad-stats">
      <button
        v-for="s in stats"
        :key="s.label"
        type="button"
        class="ad-stat"
        @click="go(s.to)"
      >
        <span class="ad-stat-ico"><svg v-if="s.icon==='users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg><svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
        <span class="ad-stat-value">{{ loading ? '…' : s.value }}</span>
        <span class="ad-stat-label">{{ s.label }}</span>
        <span class="ad-stat-hint">{{ s.hint }}</span>
      </button>
    </div>

    <div class="ad-recent">
      <h3>Appointment Terbaru</h3>
      <div v-if="recent.length === 0 && !loading" class="ad-empty">Belum ada data appointment.</div>
      <table v-else class="ad-table">
        <thead>
          <tr><th>Pasien</th><th>Dokter</th><th>Jadwal</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr v-for="r in recent" :key="r.id">
            <td>{{ r.patient?.full_name ?? r.patient ?? '-' }}</td>
            <td>{{ r.doctor?.full_name ?? r.doctor ?? '-' }}</td>
            <td>{{ fmtDate(r.scheduled_at) }}</td>
            <td><span class="ad-chip">{{ r.status ?? '-' }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.ad-dash { color: #1f2937; }
.ad-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
.ad-head h2 { margin: 0; font-size: 1.4rem; font-weight: 750; }
.ad-head p { margin: 0.2rem 0 0; color: #6b7280; font-size: 0.85rem; }
.ad-refresh { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.ad-refresh:disabled { opacity: 0.6; cursor: progress; }
.ad-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; padding: 0.7rem 0.9rem; border-radius: 10px; font-size: 0.85rem; margin-bottom: 1rem; }

.ad-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.9rem; margin-bottom: 1.5rem; }
.ad-stat {
  display: flex; flex-direction: column; gap: 0.15rem; align-items: flex-start;
  text-align: left; padding: 1.05rem 1.1rem; border-radius: 14px;
  border: 1px solid #e5e7eb; background: #fff; cursor: pointer;
  transition: transform 0.13s ease, box-shadow 0.13s ease;
}
.ad-stat:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(31, 41, 55, 0.08); }
.ad-stat-ico { width: 38px; height: 38px; border-radius: 10px; background: #eef2ff; color: #4338ca; display: grid; place-items: center; margin-bottom: 0.45rem; }
.ad-stat-value { font-size: 1.6rem; font-weight: 750; line-height: 1; }
.ad-stat-label { font-size: 0.82rem; font-weight: 650; color: #374151; }
.ad-stat-hint { font-size: 0.72rem; color: #9ca3af; }

.ad-recent { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 1.1rem 1.2rem; }
.ad-recent h3 { margin: 0 0 0.85rem; font-size: 1rem; font-weight: 700; }
.ad-empty { color: #9ca3af; font-size: 0.85rem; padding: 1rem 0; }
.ad-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.ad-table th { text-align: left; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; padding: 0 0.6rem 0.5rem; border-bottom: 1px solid #e5e7eb; }
.ad-table td { padding: 0.6rem; border-bottom: 1px solid #f3f4f6; color: #374151; }
.ad-chip { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px; background: #eef2ff; color: #4338ca; font-size: 0.72rem; font-weight: 650; }
</style>
