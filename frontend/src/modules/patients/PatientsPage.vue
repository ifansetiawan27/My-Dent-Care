<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'

const route = useRoute()

/* ===== State ===== */
const data = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const searchQ = ref('')
const showModal = ref(false)
const showDetail = ref(false)
const selected = ref<any>(null)
const saving = ref(false)
const saveMsg = ref('')
const formData = ref<Record<string, any>>({})
const currentPage = ref(1)
const perPage = ref(10)

/* ===== Stats ===== */
const stats = computed(() => {
  const all = data.value
  return {
    total: all.length,
    male: all.filter(p => p.gender === 'male').length,
    female: all.filter(p => p.gender === 'female').length,
    active: all.filter(p => p.status !== 'inactive').length,
  }
})

const filteredData = computed(() => {
  let list = data.value
  if (searchQ.value) {
    const q = searchQ.value.toLowerCase()
    list = list.filter(p =>
      p.full_name?.toLowerCase().includes(q) ||
      p.patient_code?.toLowerCase().includes(q) ||
      p.phone?.toLowerCase().includes(q) ||
      p.email?.toLowerCase().includes(q)
    )
  }
  return list
})

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredData.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value))

/* ===== API ===== */
async function fetchData(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const { data: res } = await api.get<ApiResponse<any[]>>('/v1/patients')
    data.value = res.data ?? []
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data.'
  } finally {
    loading.value = false
  }
}

/* ===== Actions ===== */
function openCreate() {
  formData.value = {
    status: 'active',
    patient_type: 'regular',
  }
  saveMsg.value = ''
  showModal.value = true
}

function openDetail(item: any) {
  selected.value = item
  showDetail.value = true
}

function editItem(item: any) {
  formData.value = { ...item }
  saveMsg.value = ''
  showModal.value = true
}

async function handleSave() {
  saving.value = true
  saveMsg.value = ''
  try {
    if (formData.value.id) {
      await api.put(`/v1/patients/${formData.value.id}`, formData.value)
    } else {
      await api.post('/v1/patients', formData.value)
    }
    saveMsg.value = 'Berhasil disimpan.'
    showModal.value = false
    await fetchData()
  } catch (e: any) {
    saveMsg.value = e?.response?.data?.message ?? e?.message ?? 'Gagal menyimpan.'
  } finally {
    saving.value = false
  }
}

async function handleDelete(id: string) {
  if (!confirm('Hapus data pasien ini?')) return
  try {
    await api.delete(`/v1/patients/${id}`)
    data.value = data.value.filter((d: any) => d.id !== id)
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal menghapus.'
  }
}

/* ===== Helpers ===== */
function formatBirthDate(val: string): string {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

function calcAge(birthDate: string): number {
  if (!birthDate) return 0
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const m = today.getMonth() - birth.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--
  return age
}

function genderLabel(g: string): string {
  return g === 'male' ? 'Laki-laki' : g === 'female' ? 'Perempuan' : g || '—'
}

function genderIcon(g: string): string {
  return g === 'male' ? 'M' : 'F'
}

function statusBadgeClass(status: string): string {
  return status === 'active' ? 'badge-green' : status === 'inactive' ? 'badge-gray' : 'badge-blue'
}

function statusLabel(status: string): string {
  return status === 'active' ? 'Aktif' : status === 'inactive' ? 'Tidak Aktif' : status || '—'
}

function bloodTypeLabel(bt: string): string {
  const map: Record<string, string> = { A: 'A', B: 'B', AB: 'AB', O: 'O' }
  return map[bt] || '—'
}

function patientTypeName(pt: string): string {
  const map: Record<string, string> = { regular: 'Reguler', bpjs: 'BPJS', vip: 'VIP', corporate: 'Korporat' }
  return map[pt] || 'Reguler'
}

onMounted(() => { fetchData() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="pt-head">
      <div>
        <h1 class="pt-title">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Data Pasien
        </h1>
        <p class="pt-desc">Kelola data pasien klinik, nomor rekam medis, dan informasi demografis.</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Pasien
      </button>
    </div>

    <!-- Stat Cards -->
    <div class="pt-stats">
      <div class="stat-card stat-total">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total Pasien</span>
        </div>
      </div>
      <div class="stat-card stat-male">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.male }}</span>
          <span class="stat-label">Laki-laki</span>
        </div>
      </div>
      <div class="stat-card stat-female">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.female }}</span>
          <span class="stat-label">Perempuan</span>
        </div>
      </div>
      <div class="stat-card stat-active">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.active }}</span>
          <span class="stat-label">Aktif</span>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="pt-toolbar">
      <div class="pt-search-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="pt-search-icon">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input v-model="searchQ" type="text" placeholder="Cari nama, no. RM, atau telepon..." class="pt-search" />
      </div>
      <button class="btn btn-ghost btn-sm" @click="fetchData">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="pt-status">
      <div class="spinner" style="margin:0 auto 1rem"></div>
      Memuat data pasien...
    </div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <!-- Empty -->
    <div v-else-if="!filteredData.length" class="pt-empty">
      <div class="pt-empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
      <h3>Belum ada data pasien</h3>
      <p>Klik tombol "Tambah Pasien" untuk membuat data baru.</p>
    </div>

    <!-- Table -->
    <div v-else class="pt-table-wrap">
      <table class="pt-table">
        <thead>
          <tr>
            <th>No. RM</th>
            <th>Nama Pasien</th>
            <th>JK</th>
            <th>Tgl Lahir</th>
            <th>Telepon</th>
            <th>Tipe</th>
            <th>Status</th>
            <th style="width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in paginatedData" :key="row.id" class="pt-row">
            <td>
              <span class="rm-code">{{ row.patient_code }}</span>
            </td>
            <td>
              <div class="pt-patient">
                <span :class="['pt-avatar', row.gender === 'male' ? 'pt-avatar-m' : 'pt-avatar-f']">
                  {{ genderIcon(row.gender) }}
                </span>
                <div class="pt-patient-info">
                  <span class="pt-patient-name">{{ row.full_name }}</span>
                  <span v-if="row.email" class="pt-patient-email">{{ row.email }}</span>
                </div>
              </div>
            </td>
            <td>
              <span class="gender-tag" :class="row.gender === 'male' ? 'gender-m' : 'gender-f'">
                {{ genderLabel(row.gender) }}
              </span>
            </td>
            <td>
              <div class="pt-birth">
                <span class="pt-birth-date">{{ formatBirthDate(row.birth_date) }}</span>
                <span class="pt-age">{{ calcAge(row.birth_date) }} th</span>
              </div>
            </td>
            <td>{{ row.phone || '—' }}</td>
            <td><span class="type-badge">{{ patientTypeName(row.patient_type) }}</span></td>
            <td><span :class="'badge ' + statusBadgeClass(row.status)">{{ statusLabel(row.status) }}</span></td>
            <td>
              <div class="pt-actions">
                <button class="btn-icon" title="Detail" @click="openDetail(row)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
                <button class="btn-icon" title="Edit" @click="editItem(row)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button class="btn-icon btn-icon-danger" title="Hapus" @click="handleDelete(row.id)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="filteredData.length" class="pt-pagination">
      <span class="pt-page-info">
        Menampilkan {{ ((currentPage - 1) * perPage) + 1 }}–{{ Math.min(currentPage * perPage, filteredData.length) }} dari {{ filteredData.length }} data
      </span>
      <div class="pt-page-btns">
        <button class="btn-page" :disabled="currentPage === 1" @click="currentPage--">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button
          v-for="p in totalPages"
          :key="p"
          :class="['btn-page-num', { active: p === currentPage }]"
          @click="currentPage = p"
        >{{ p }}</button>
        <button class="btn-page" :disabled="currentPage === totalPages" @click="currentPage++">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
      </div>
    </div>

    <!-- Detail Modal -->
    <transition name="fade">
      <div v-if="showDetail" class="modal-overlay" @click.self="showDetail = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>Detail Pasien</h3>
            <button class="modal-close" @click="showDetail = false">&times;</button>
          </div>
          <div v-if="selected" class="modal-body">
            <div class="detail-header">
              <span :class="['detail-avatar', selected.gender === 'male' ? 'pt-avatar-m' : 'pt-avatar-f']">
                {{ genderIcon(selected.gender) }}
              </span>
              <div class="detail-name-wrap">
                <h4 class="detail-name">{{ selected.full_name }}</h4>
                <span class="detail-rm">{{ selected.patient_code }}</span>
              </div>
            </div>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Jenis Kelamin</span>
                <span class="detail-value">{{ genderLabel(selected.gender) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tanggal Lahir</span>
                <span class="detail-value">{{ formatBirthDate(selected.birth_date) }} ({{ calcAge(selected.birth_date) }} tahun)</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Gol. Darah</span>
                <span class="detail-value">{{ bloodTypeLabel(selected.blood_type) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Agama</span>
                <span class="detail-value">{{ selected.religion || '—' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Status Pernikahan</span>
                <span class="detail-value">{{ selected.marital_status || '—' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tipe Pasien</span>
                <span class="detail-value">{{ patientTypeName(selected.patient_type) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Telepon</span>
                <span class="detail-value">{{ selected.phone || '—' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ selected.email || '—' }}</span>
              </div>
              <div class="detail-item full">
                <span class="detail-label">Alamat</span>
                <span class="detail-value">{{ selected.address || '—' }}</span>
              </div>
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showDetail = false">Tutup</button>
            <button class="btn btn-primary" @click="editItem(selected); showDetail = false">Edit</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Create/Edit Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>{{ formData.id ? 'Edit' : 'Tambah' }} Pasien</h3>
            <button class="modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>No. Rekam Medis</label>
                <input v-model="formData.patient_code" type="text" class="form-input" placeholder="RM-0001" />
              </div>
              <div class="form-group">
                <label>Nama Lengkap <span class="req">*</span></label>
                <input v-model="formData.full_name" type="text" class="form-input" required placeholder="Nama pasien" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Tanggal Lahir</label>
                <input v-model="formData.birth_date" type="date" class="form-input" />
              </div>
              <div class="form-group">
                <label>Jenis Kelamin</label>
                <select v-model="formData.gender" class="form-input">
                  <option value="">— Pilih —</option>
                  <option value="male">Laki-laki</option>
                  <option value="female">Perempuan</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Gol. Darah</label>
                <select v-model="formData.blood_type" class="form-input">
                  <option value="">— Pilih —</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="AB">AB</option>
                  <option value="O">O</option>
                </select>
              </div>
              <div class="form-group">
                <label>Tipe Pasien</label>
                <select v-model="formData.patient_type" class="form-input">
                  <option value="regular">Reguler</option>
                  <option value="bpjs">BPJS</option>
                  <option value="vip">VIP</option>
                  <option value="corporate">Korporat</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Telepon</label>
                <input v-model="formData.phone" type="text" class="form-input" placeholder="+62 812 xxxx xxxx" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="formData.email" type="email" class="form-input" placeholder="email@contoh.com" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Agama</label>
                <input v-model="formData.religion" type="text" class="form-input" />
              </div>
              <div class="form-group">
                <label>Status Pernikahan</label>
                <select v-model="formData.marital_status" class="form-input">
                  <option value="">— Pilih —</option>
                  <option value="single">Belum Menikah</option>
                  <option value="married">Menikah</option>
                  <option value="divorced">Cerai</option>
                  <option value="widowed">Janda/Duda</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea v-model="formData.address" class="form-input form-textarea" placeholder="Alamat lengkap pasien..."></textarea>
            </div>
            <div v-if="saveMsg" :class="saveMsg.includes('Gagal') ? 'alert alert-error' : 'alert alert-success'">{{ saveMsg }}</div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showModal = false">Batal</button>
            <button class="btn btn-primary" :disabled="saving" @click="handleSave">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* ===== Header ===== */
.pt-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.pt-title { display: flex; align-items: center; gap: 0.625rem; font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
.pt-desc { font-size: 0.875rem; color: #64748b; margin: 0; }

/* ===== Stats ===== */
.pt-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.125rem; border-radius: 14px; background: #fff; border: 1px solid #e5e7eb; }
.stat-icon { display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 10px; }
.stat-total .stat-icon { background: #f1f5f9; color: #475569; }
.stat-male .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-female .stat-icon { background: #fce7f3; color: #db2777; }
.stat-active .stat-icon { background: #d1fae5; color: #059669; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.375rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.stat-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

/* ===== Toolbar ===== */
.pt-toolbar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.pt-search-wrap { position: relative; flex: 1; min-width: 240px; }
.pt-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
.pt-search { width: 100%; padding: 0.625rem 0.625rem 0.625rem 2.5rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; font-family: inherit; outline: none; background: #fff; }
.pt-search:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }

/* ===== Table ===== */
.pt-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow-x: auto; }
.pt-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.pt-table th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; font-size: 0.6875rem; padding: 0.75rem 0.875rem; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
.pt-table td { padding: 0.75rem 0.875rem; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.pt-table tr:hover td { background: #f8fafc; }

/* ===== RM Code ===== */
.rm-code { font-family: 'Cascadia Code', 'Fira Code', monospace; font-size: 0.75rem; font-weight: 700; color: #0ea5e9; background: #f0f9ff; padding: 0.25rem 0.5rem; border-radius: 6px; }

/* ===== Patient cell ===== */
.pt-patient { display: flex; align-items: center; gap: 0.625rem; }
.pt-avatar { width: 34px; height: 34px; border-radius: 50%; font-weight: 700; font-size: 0.8125rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pt-avatar-m { background: #dbeafe; color: #2563eb; }
.pt-avatar-f { background: #fce7f3; color: #db2777; }
.pt-patient-info { display: flex; flex-direction: column; }
.pt-patient-name { font-weight: 600; color: #0f172a; }
.pt-patient-email { font-size: 0.6875rem; color: #94a3b8; }

/* ===== Gender ===== */
.gender-tag { font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 6px; white-space: nowrap; }
.gender-m { background: #dbeafe; color: #2563eb; }
.gender-f { background: #fce7f3; color: #db2777; }

/* ===== Birth date ===== */
.pt-birth { display: flex; flex-direction: column; }
.pt-birth-date { font-weight: 500; color: #334155; }
.pt-age { font-size: 0.6875rem; color: #94a3b8; }

/* ===== Type badge ===== */
.type-badge { font-size: 0.6875rem; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 0.1875rem 0.5rem; border-radius: 6px; }

/* ===== Status badges ===== */
.badge { display: inline-block; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; white-space: nowrap; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-gray { background: #f1f5f9; color: #475569; }
.badge-blue { background: #dbeafe; color: #1e40af; }

/* ===== Actions ===== */
.pt-actions { display: flex; align-items: center; gap: 0.375rem; }
.btn-icon { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 6px; background: #f1f5f9; color: #475569; cursor: pointer; }
.btn-icon:hover { background: #e2e8f0; }
.btn-icon-danger { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 6px; background: #fef2f2; color: #ef4444; cursor: pointer; }
.btn-icon-danger:hover { background: #fee2e2; }

/* ===== Pagination ===== */
.pt-pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 0.75rem 0; }
.pt-page-info { font-size: 0.75rem; color: #94a3b8; }
.pt-page-btns { display: flex; align-items: center; gap: 0.25rem; }
.btn-page { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #64748b; cursor: pointer; }
.btn-page:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-page:not(:disabled):hover { background: #f1f5f9; }
.btn-page-num { width: 32px; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #334155; font-size: 0.8125rem; font-weight: 600; cursor: pointer; }
.btn-page-num.active { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.btn-page-num:not(.active):hover { background: #f1f5f9; }

/* ===== Empty ===== */
.pt-empty { text-align: center; padding: 3rem 1rem; }
.pt-empty-icon { color: #e2e8f0; margin-bottom: 1rem; }
.pt-empty h3 { font-size: 1.125rem; color: #0f172a; margin: 0 0 0.5rem; }
.pt-empty p { font-size: 0.875rem; color: #94a3b8; margin: 0; }

/* ===== Status ===== */
.pt-status { text-align: center; padding: 3rem; color: #64748b; font-size: 0.9375rem; }

/* ===== Detail Modal ===== */
.detail-header { display: flex; align-items: center; gap: 1rem; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
.detail-avatar { width: 56px; height: 56px; border-radius: 50%; font-weight: 800; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.detail-name-wrap { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-name { font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0; }
.detail-rm { font-family: 'Cascadia Code', 'Fira Code', monospace; font-size: 0.75rem; font-weight: 700; color: #0ea5e9; }

.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-item.full { grid-column: 1 / -1; }
.detail-label { font-size: 0.6875rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
.detail-value { font-size: 0.9375rem; color: #0f172a; font-weight: 500; }

/* ===== Modal ===== */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); display: flex; align-items: center; justify-content: center; z-index: 200; }
.modal { background: #fff; border-radius: 16px; width: 95%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 30px 80px rgba(2,6,23,0.25); }
.modal-lg { max-width: 720px; }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-head h3 { margin: 0; font-size: 1.125rem; font-weight: 800; color: #0f172a; }
.modal-close { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; }
.modal-body { padding: 1.5rem; }
.modal-foot { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.625rem; justify-content: flex-end; }

/* ===== Form ===== */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group:not(.form-row .form-group) { margin-bottom: 1rem; }
.form-group label { font-size: 0.8125rem; font-weight: 600; color: #374151; }
.req { color: #ef4444; margin-left: 2px; }
.form-input { width: 100%; padding: 0.5rem 0.625rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; font-family: inherit; outline: none; }
.form-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.form-textarea { min-height: 80px; resize: vertical; }

/* ===== Buttons ===== */
.btn { display: inline-flex; align-items: center; gap: 0.4375rem; padding: 0.5625rem 1.125rem; border: none; border-radius: 9px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
.btn-primary { background: linear-gradient(135deg, #0ea5e9, #14b8a6); color: #fff; box-shadow: 0 6px 16px rgba(14,165,233,0.3); }
.btn-primary:hover { transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; transform: none; }
.btn-ghost { background: transparent; color: #64748b; border: 1.5px solid #e2e8f0; }
.btn-ghost:hover { border-color: #0ea5e9; color: #0ea5e9; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }

/* ===== Alerts ===== */
.alert { padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.75rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

/* ===== Animations ===== */
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .pt-stats { grid-template-columns: repeat(2, 1fr); }
  .form-row { grid-template-columns: 1fr; }
  .detail-grid { grid-template-columns: 1fr; }
  .pt-table { font-size: 0.75rem; }
  .pt-table th, .pt-table td { padding: 0.5rem; }
  .pt-pagination { flex-direction: column; gap: 0.75rem; }
}
</style>
