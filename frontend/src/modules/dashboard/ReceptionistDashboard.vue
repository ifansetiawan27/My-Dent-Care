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
const patientCount = ref(0)

const today = todayKey()

const board = computed(() =>
  appointments.value
    .filter((a) => dateKey(a.scheduled_at) === today)
    .sort((a, b) => String(a.scheduled_at ?? '').localeCompare(String(b.scheduled_at ?? '')))
)

const waiting = computed(() => board.value.filter((a) => a.status === 'confirmed' || a.status === 'scheduled').length)
const done = computed(() => board.value.filter((a) => a.status === 'completed').length)

async function load(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const [apptRes, patientRes] = await Promise.all([
      api.get<ApiResponse<any[]>>('/v1/appointments', { params: { per_page: 100 } }),
      api.get<ApiResponse<any[]>>('/v1/patients', { params: { per_page: 100 } }),
    ])
    appointments.value = apptRes.data?.data ?? []
    patientCount.value = patientRes.data?.meta?.total ?? (patientRes.data?.data ?? []).length
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data front desk.'
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
  <div class="rd-dash">
    <div class="rd-hero">
      <div>
        <p class="rd-eyebrow">Front Desk · {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
        <h2>Selamat Datang di Meja Depan</h2>
        <p class="rd-sub">Daftarkan pasien, atur janji, dan layani antrian dengan cepat.</p>
      </div>
      <div class="rd-hero-actions">
        <button class="rd-btn" type="button" @click="go('patients')">＋ Pasien Baru</button>
        <button class="rd-btn solid" type="button" @click="go('appointments')">＋ Buat Janji</button>
      </div>
    </div>

    <p v-if="error" class="rd-error">⚠ {{ error }}</p>

    <div class="rd-metrics">
      <div class="rd-metric">
        <span class="rd-metric-value">{{ loading ? '…' : board.length }}</span>
        <span class="rd-metric-label">Janji Hari Ini</span>
      </div>
      <div class="rd-metric">
        <span class="rd-metric-value">{{ loading ? '…' : waiting }}</span>
        <span class="rd-metric-label">Menunggu</span>
      </div>
      <div class="rd-metric">
        <span class="rd-metric-value">{{ loading ? '…' : done }}</span>
        <span class="rd-metric-label">Selesai</span>
      </div>
      <div class="rd-metric">
        <span class="rd-metric-value">{{ loading ? '…' : patientCount }}</span>
        <span class="rd-metric-label">Total Pasien</span>
      </div>
    </div>

    <div class="rd-board">
      <div class="rd-board-head">
        <h3>Papan Antrian Hari Ini</h3>
        <button class="rd-refresh" type="button" :disabled="loading" @click="load">
          {{ loading ? 'Memuat…' : '↻ Perbarui' }}
        </button>
      </div>

      <div v-if="board.length === 0 && !loading" class="rd-empty">
        <p><strong>Tidak ada janji hari ini.</strong></p>
        <p>Coba buat janji baru untuk pasien.</p>
      </div>

      <div v-else class="rd-cards">
        <div
          v-for="a in board"
          :key="a.id"
          class="rd-card"
          :class="{ 'is-done': a.status === 'completed' }"
        >
          <div class="rd-card-time">{{ fmtTime(a.scheduled_at) }}</div>
          <div class="rd-card-body">
            <strong class="rd-card-name">{{ a.patient?.full_name ?? a.patient ?? 'Pasien' }}</strong>
            <span class="rd-card-meta">{{ a.type ?? 'Kunjungan' }} · {{ a.doctor?.full_name ?? a.doctor ?? '-' }}</span>
          </div>
          <span class="rd-chip" :class="`st-${a.status ?? 'scheduled'}`">{{ a.status ?? 'scheduled' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rd-dash { color: #0f172a; }
.rd-hero {
  display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; flex-wrap: wrap;
  background: linear-gradient(120deg, #2563eb, #1d4ed8);
  color: #fff; border-radius: 18px; padding: 1.4rem 1.6rem; margin-bottom: 1.4rem;
}
.rd-eyebrow { margin: 0 0 0.3rem; font-size: 0.76rem; font-weight: 650; letter-spacing: 0.05em; text-transform: uppercase; color: #bfdbfe; }
.rd-hero h2 { margin: 0; font-size: 1.6rem; font-weight: 750; }
.rd-sub { margin: 0.3rem 0 0; font-size: 0.85rem; color: #dbeafe; }
.rd-hero-actions { display: flex; gap: 0.55rem; flex-wrap: wrap; }
.rd-btn {
  padding: 0.55rem 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.5);
  background: rgba(255,255,255,0.12); color: #fff; font-size: 0.84rem; font-weight: 700; cursor: pointer;
}
.rd-btn:hover { background: rgba(255,255,255,0.22); }
.rd-btn.solid { background: #fff; color: #1d4ed8; border-color: #fff; }

.rd-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; padding: 0.7rem 0.9rem; border-radius: 10px; font-size: 0.85rem; margin-bottom: 1rem; }

.rd-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.85rem; margin-bottom: 1.4rem; }
.rd-metric {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
  padding: 1rem 1.1rem; display: flex; flex-direction: column; gap: 0.15rem;
}
.rd-metric-value { font-size: 1.5rem; font-weight: 750; color: #1d4ed8; line-height: 1.1; }
.rd-metric-label { font-size: 0.76rem; font-weight: 650; color: #64748b; }

.rd-board { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.1rem 1.2rem; }
.rd-board-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem; }
.rd-board-head h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.rd-refresh { background: none; border: none; color: #2563eb; font-size: 0.82rem; font-weight: 700; cursor: pointer; }
.rd-refresh:disabled { opacity: 0.5; cursor: progress; }
.rd-empty { text-align: center; padding: 2rem 1rem; color: #94a3b8; }
.rd-empty p { margin: 0.2rem 0; font-size: 0.88rem; }
.rd-empty strong { color: #475569; }

.rd-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.8rem; }
.rd-card {
  display: flex; align-items: center; gap: 0.85rem;
  border: 1px solid #e2e8f0; border-radius: 13px; padding: 0.75rem 0.9rem;
  background: #f8fafc;
}
.rd-card.is-done { opacity: 0.62; background: #f1f5f9; }
.rd-card-time {
  font-weight: 750; font-size: 0.85rem; color: #1d4ed8;
  background: #eff6ff; border: 1px solid #dbeafe; border-radius: 9px;
  padding: 0.4rem 0.55rem; min-width: 58px; text-align: center;
}
.rd-card-body { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.rd-card-name { font-size: 0.88rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rd-card-meta { font-size: 0.72rem; color: #94a3b8; }
.rd-chip { padding: 0.15rem 0.55rem; border-radius: 999px; font-size: 0.68rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
.rd-chip.st-completed { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.rd-chip.st-cancelled, .rd-chip.st-no_show { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
</style>
