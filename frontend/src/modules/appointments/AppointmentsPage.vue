<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'

const route = useRoute()

/* ===== State ===== */
const data = ref<any[]>([])
const doctors = ref<any[]>([])
const patients = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const viewMode = ref<'list' | 'calendar'>('list')
const showModal = ref(false)
const showDetail = ref(false)
const selected = ref<any>(null)
const saving = ref(false)
const saveMsg = ref('')
const formData = ref<Record<string, any>>({})

/* ===== WhatsApp ===== */
const waStatus = ref({ status: 'disconnected' })
const showWaModal = ref(false)
const sendingReminder = ref(false)
const reminderMsg = ref('')

/* ===== Filters ===== */
const filterDate = ref<string>('')
const filterStatus = ref<string>('')
const filterDoctor = ref<string>('')
const searchQ = ref('')

/* ===== Stats ===== */
const stats = computed(() => {
  const all = data.value
  return {
    total: all.length,
    scheduled: all.filter(a => a.status === 'scheduled' || a.status === 'confirmed').length,
    completed: all.filter(a => a.status === 'completed').length,
    cancelled: all.filter(a => a.status === 'cancelled' || a.status === 'no_show').length,
  }
})

const filteredData = computed(() => {
  let list = data.value
  if (filterDate.value) {
    list = list.filter(a => a.scheduled_at?.startsWith(filterDate.value))
  }
  if (filterStatus.value) {
    list = list.filter(a => a.status === filterStatus.value)
  }
  if (filterDoctor.value) {
    list = list.filter(a => a.doctor_id === filterDoctor.value || a.doctor?.id === filterDoctor.value)
  }
  if (searchQ.value) {
    const q = searchQ.value.toLowerCase()
    list = list.filter(a =>
      a.patient?.full_name?.toLowerCase().includes(q) ||
      a.doctor?.full_name?.toLowerCase().includes(q) ||
      a.patient_code?.toLowerCase().includes(q) ||
      a.notes?.toLowerCase().includes(q)
    )
  }
  return list
})

/* ===== Calendar data ===== */
const calendarMonth = ref(new Date().getMonth())
const calendarYear = ref(new Date().getFullYear())

const calendarDays = computed(() => {
  const year = calendarYear.value
  const month = calendarMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDay = firstDay.getDay()
  const daysInMonth = lastDay.getDate()

  const days: { date: string; day: number; appointments: any[] }[] = []
  // padding
  for (let i = 0; i < startDay; i++) {
    days.push({ date: '', day: 0, appointments: [] })
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    const appts = data.value.filter(a => a.scheduled_at?.startsWith(dateStr))
    days.push({ date: dateStr, day: d, appointments: appts })
  }
  return days
})

const monthName = computed(() => {
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  return `${months[calendarMonth.value]} ${calendarYear.value}`
})

function prevMonth() {
  if (calendarMonth.value === 0) {
    calendarMonth.value = 11
    calendarYear.value--
  } else {
    calendarMonth.value--
  }
}
function nextMonth() {
  if (calendarMonth.value === 11) {
    calendarMonth.value = 0
    calendarYear.value++
  } else {
    calendarMonth.value++
  }
}

/* ===== API ===== */
async function fetchData(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const [apptsRes, doctorsRes, patientsRes] = await Promise.all([
      api.get<ApiResponse<any[]>>('/v1/appointments'),
      api.get<ApiResponse<any[]>>('/v1/doctors'),
      api.get<ApiResponse<any[]>>('/v1/patients'),
    ])
    data.value = apptsRes.data.data ?? []
    doctors.value = doctorsRes.data.data ?? []
    patients.value = patientsRes.data.data ?? []
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data.'
  } finally {
    loading.value = false
  }
}

async function fetchWaStatus(): Promise<void> {
  try {
    const { data: res } = await api.get('/v1/whatsapp/status')
    waStatus.value = res
  } catch { /* ignore */ }
}

/* ===== WhatsApp Reminder ===== */
async function sendManualReminder(appointment: any): Promise<void> {
  selected.value = appointment
  showWaModal.value = true
  reminderMsg.value = ''
  sendingReminder.value = false

  const patientName = appointment.patient?.full_name || 'Pasien'
  const doctorName = appointment.doctor?.full_name || 'Dokter'
  const scheduledAt = appointment.scheduled_at
  const formattedDate = scheduledAt ? new Date(scheduledAt).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }) : ''
  const formattedTime = scheduledAt ? new Date(scheduledAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : ''
  const typeLabel = (appointment.type || '').charAt(0).toUpperCase() + (appointment.type || '').slice(1)

  reminderMsg.value = `🦷 *My Dent Care - Appointment Reminder*

Halo, ${patientName}! 👋

Ini adalah pengingat untuk janji temu Anda:

📅 *${formattedDate}*
⏰ *${formattedTime}*
👨‍⚕️ *${doctorName}*
📋 *${typeLabel}*

Mohon hadir 15 menit sebelum jadwal.

_Terima kasih!_ 😊`
}

async function executeReminder(): Promise<void> {
  if (!selected.value) return
  sendingReminder.value = true
  try {
    const patientPhone = selected.value.patient?.phone
    if (!patientPhone) {
      reminderMsg.value = 'Error: Nomor telepon pasien tidak tersedia.'
      return
    }

    await api.post('/v1/whatsapp/test-send', {
      phone: patientPhone,
      message: reminderMsg.value,
    })
    reminderMsg.value = '✅ Reminder berhasil dikirim!'
  } catch (e: any) {
    reminderMsg.value = '❌ Gagal mengirim reminder: ' + (e?.response?.data?.message || e?.message)
  } finally {
    sendingReminder.value = false
  }
}

/* ===== Actions ===== */
function openCreate() {
  formData.value = {
    scheduled_at: new Date().toISOString().slice(0, 16),
    status: 'scheduled',
    type: 'checkup',
    reminder_minutes: 60,
  }
  saveMsg.value = ''
  showModal.value = true
}

function openDetail(item: any) {
  selected.value = item
  showDetail.value = true
}

function currentUser(): Record<string, any> {
  try { return JSON.parse(localStorage.getItem('auth_user') || '{}') } catch { return {} }
}

async function handleSave() {
  saving.value = true
  saveMsg.value = ''
  try {
    const u = currentUser()
    const payload: Record<string, any> = {
      ...formData.value,
      organization_id: u.organization_id,
      branch_id: u.branch_id || null,
    }
    if (formData.value.id) {
      await api.put(`/v1/appointments/${formData.value.id}`, payload)
    } else {
      await api.post('/v1/appointments', payload)
    }
    saveMsg.value = 'Berhasil disimpan.'
    showModal.value = false
    await fetchData()
  } catch (e: any) {
    const errs = e?.response?.data?.errors
    const firstField = errs ? Object.values(errs).flat()[0] : null
    saveMsg.value = firstField ?? e?.response?.data?.message ?? e?.message ?? 'Gagal menyimpan.'
  } finally {
    saving.value = false
  }
}

async function updateStatus(id: string, status: string) {
  try {
    await api.patch(`/v1/appointments/${id}`, { status })
    await fetchData()
  } catch {
    error.value = 'Gagal mengubah status.'
  }
}

async function handleDelete(id: string) {
  if (!confirm('Hapus appointment ini?')) return
  try {
    await api.delete(`/v1/appointments/${id}`)
    data.value = data.value.filter((d: any) => d.id !== id)
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal menghapus.'
  }
}

function editItem(item: any) {
  formData.value = { ...item }
  saveMsg.value = ''
  showModal.value = true
}

/* ===== Helpers ===== */
function formatDateTime(val: string): string {
  if (!val) return '—'
  return new Date(val).toLocaleString('id-ID', {
    weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}

function formatDateShort(val: string): string {
  if (!val) return ''
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
}

function formatTime(val: string): string {
  if (!val) return ''
  return new Date(val).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function badgeClass(status: string): string {
  const map: Record<string, string> = {
    scheduled: 'badge-blue', confirmed: 'badge-cyan', completed: 'badge-green',
    cancelled: 'badge-red', no_show: 'badge-orange',
  }
  return map[status] || 'badge-gray'
}

function badgeLabel(status: string): string {
  const map: Record<string, string> = {
    scheduled: 'Terjadwal', confirmed: 'Dikonfirmasi', completed: 'Selesai',
    cancelled: 'Dibatalkan', no_show: 'Tidak Hadir',
  }
  return map[status] || status
}

function typeLabel(type: string): string {
  const map: Record<string, string> = {
    checkup: 'Check-up', treatment: 'Perawatan', consultation: 'Konsultasi',
    follow_up: 'Follow-up', emergency: 'Darurat',
  }
  return map[type] || type
}

function reminderMinutesLabel(minutes: number | null): string {
  if (!minutes) return '—'
  const map: Record<number, string> = {
    30: '30 menit', 60: '1 jam', 120: '2 jam',
    240: '4 jam', 360: '6 jam', 720: '12 jam',
  }
  return map[minutes] || `${minutes} menit`
}

function patientName(row: any): string {
  return row.patient?.full_name || row.patient_name || row.patient_code || '—'
}

function doctorName(row: any): string {
  return row.doctor?.full_name || row.doctor_name || '—'
}

const todayStr = computed(() => new Date().toISOString().slice(0, 10))

onMounted(() => { fetchData(); fetchWaStatus() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="appt-head">
      <div>
        <h1 class="appt-title">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Appointment
          <span :class="['wa-badge', waStatus.status === 'connected' ? 'wa-connected' : 'wa-disconnected']">
            <svg fill="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.662 1.438 5.168L2 22l5.038-1.36A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.66 0-3.203-.523-4.467-1.413l-.302-.205-3.076.83.84-3.033-.213-.326A7.963 7.963 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
            {{ waStatus.status === 'connected' ? 'WA Aktif' : 'WA Tidak Aktif' }}
          </span>
        </h1>
        <p class="appt-desc">Kelola jadwal janji temu pasien dan pantau status kunjungan.</p>
      </div>
      <div class="appt-actions-row">
        <button class="btn btn-ghost btn-sm" @click="fetchWaStatus">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          WA Status
        </button>
        <button class="btn btn-primary" @click="openCreate">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Appointment
        </button>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="appt-stats">
      <div class="stat-card stat-total">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total</span>
        </div>
      </div>
      <div class="stat-card stat-scheduled">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.scheduled }}</span>
          <span class="stat-label">Terjadwal</span>
        </div>
      </div>
      <div class="stat-card stat-completed">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.completed }}</span>
          <span class="stat-label">Selesai</span>
        </div>
      </div>
      <div class="stat-card stat-cancelled">
        <span class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <div class="stat-info">
          <span class="stat-value">{{ stats.cancelled }}</span>
          <span class="stat-label">Dibatalkan</span>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="appt-filters">
      <div class="filter-group">
        <label>Tanggal</label>
        <input v-model="filterDate" type="date" class="filter-input" />
      </div>
      <div class="filter-group">
        <label>Status</label>
        <select v-model="filterStatus" class="filter-input">
          <option value="">Semua</option>
          <option value="scheduled">Terjadwal</option>
          <option value="confirmed">Dikonfirmasi</option>
          <option value="completed">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
          <option value="no_show">Tidak Hadir</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Dokter</label>
        <select v-model="filterDoctor" class="filter-input">
          <option value="">Semua Dokter</option>
          <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.full_name }}</option>
        </select>
      </div>
      <div class="filter-group filter-search">
        <input v-model="searchQ" type="text" placeholder="Cari pasien / dokter..." class="filter-input" />
      </div>
      <div class="filter-actions">
        <button class="btn btn-ghost btn-sm" @click="fetchData">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="appt-toolbar">
      <div class="view-toggle">
        <button :class="['toggle-btn', { active: viewMode === 'list' }]" @click="viewMode = 'list'">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
          List
        </button>
        <button :class="['toggle-btn', { active: viewMode === 'calendar' }]" @click="viewMode = 'calendar'">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          Kalender
        </button>
      </div>
      <span class="result-count">{{ filteredData.length }} appointment</span>
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="appt-status">
      <div class="spinner" style="margin:0 auto 1rem"></div>
      Memuat data...
    </div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <!-- List View -->
    <div v-else-if="viewMode === 'list'" class="appt-table-wrap">
      <table class="appt-table">
        <thead>
          <tr>
            <th>Pasien</th>
            <th>Dokter</th>
            <th>Jadwal</th>
            <th>Tipe</th>
            <th>Status</th>
            <th>Reminder</th>
            <th>Catatan</th>
            <th style="width:180px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in filteredData" :key="row.id" class="appt-row">
            <td>
              <div class="appt-patient">
                <span class="appt-patient-avatar">{{ patientName(row).charAt(0) }}</span>
                <span class="appt-patient-name">{{ patientName(row) }}</span>
              </div>
            </td>
            <td>{{ doctorName(row) }}</td>
            <td>
              <div class="appt-datetime">
                <span class="appt-date">{{ formatDateShort(row.scheduled_at) }}</span>
                <span class="appt-time">{{ formatTime(row.scheduled_at) }}</span>
              </div>
            </td>
            <td><span class="type-tag">{{ typeLabel(row.type) }}</span></td>
            <td><span :class="'badge ' + badgeClass(row.status)">{{ badgeLabel(row.status) }}</span></td>
            <td>
              <span v-if="row.reminder_minutes" class="reminder-tag" :class="row.reminder_sent ? 'reminder-sent' : 'reminder-pending'">
                {{ reminderMinutesLabel(row.reminder_minutes) }}
                <span v-if="row.reminder_sent" class="reminder-check">✓</span>
              </span>
              <span v-else class="reminder-tag reminder-none">—</span>
            </td>
            <td class="appt-notes-cell">{{ row.notes || '—' }}</td>
            <td>
              <div class="appt-actions">
                <button class="btn-icon" title="Detail" @click="openDetail(row)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
                <button class="btn-icon" title="Edit" @click="editItem(row)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button v-if="row.status === 'scheduled' || row.status === 'confirmed'" class="btn-icon btn-icon-wa" title="Kirim WA Reminder" @click="sendManualReminder(row)">
                  <svg fill="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.662 1.438 5.168L2 22l5.038-1.36A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.66 0-3.203-.523-4.467-1.413l-.302-.205-3.076.83.84-3.033-.213-.326A7.963 7.963 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
                </button>
                <select
                  :value="row.status"
                  class="status-select"
                  @change="updateStatus(row.id, ($event.target as HTMLSelectElement).value)"
                >
                  <option value="scheduled">Terjadwal</option>
                  <option value="confirmed">Dikonfirmasi</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Dibatalkan</option>
                  <option value="no_show">Tidak Hadir</option>
                </select>
                <button class="btn-icon btn-icon-danger" title="Hapus" @click="handleDelete(row.id)">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!filteredData.length">
            <td colspan="7" class="appt-empty">Tidak ada appointment yang sesuai filter.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Calendar View -->
    <div v-else-if="!loading" class="appt-calendar">
      <div class="cal-header">
        <button class="cal-nav" @click="prevMonth">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <h2 class="cal-month">{{ monthName }}</h2>
        <button class="cal-nav" @click="nextMonth">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
      </div>
      <div class="cal-weekdays">
        <span v-for="d in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="d" class="cal-weekday">{{ d }}</span>
      </div>
      <div class="cal-grid">
        <div
          v-for="(day, idx) in calendarDays"
          :key="idx"
          :class="['cal-day', { 'cal-day-empty': !day.day, 'cal-today': day.date === todayStr }]"
        >
          <span v-if="day.day" class="cal-day-num">{{ day.day }}</span>
          <div v-if="day.appointments.length" class="cal-events">
            <div
              v-for="appt in day.appointments.slice(0, 3)"
              :key="appt.id"
              :class="['cal-event', 'cal-event-' + (appt.status || 'scheduled')]"
              @click="openDetail(appt)"
            >
              <span class="cal-event-dot"></span>
              <span class="cal-event-text">{{ formatTime(appt.scheduled_at) }} – {{ patientName(appt) }}</span>
            </div>
            <div v-if="day.appointments.length > 3" class="cal-more">+{{ day.appointments.length - 3 }} lainnya</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <transition name="fade">
      <div v-if="showDetail" class="modal-overlay" @click.self="showDetail = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>Detail Appointment</h3>
            <button class="modal-close" @click="showDetail = false">&times;</button>
          </div>
          <div v-if="selected" class="modal-body">
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Pasien</span>
                <span class="detail-value">{{ patientName(selected) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Dokter</span>
                <span class="detail-value">{{ doctorName(selected) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Jadwal</span>
                <span class="detail-value">{{ formatDateTime(selected.scheduled_at) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tipe</span>
                <span class="detail-value">{{ typeLabel(selected.type) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Status</span>
                <span :class="'badge ' + badgeClass(selected.status)">{{ badgeLabel(selected.status) }}</span>
              </div>
              <div class="detail-item full">
                <span class="detail-label">Catatan</span>
                <span class="detail-value">{{ selected.notes || '—' }}</span>
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
            <h3>{{ formData.id ? 'Edit' : 'Tambah' }} Appointment</h3>
            <button class="modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>Pasien <span class="req">*</span></label>
                <select v-model="formData.patient_id" class="form-input" required>
                  <option value="">— Pilih Pasien —</option>
                  <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.full_name }} ({{ p.patient_code }})</option>
                </select>
              </div>
              <div class="form-group">
                <label>Dokter</label>
                <select v-model="formData.doctor_id" class="form-input">
                  <option value="">— Pilih Dokter —</option>
                  <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.full_name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Jadwal <span class="req">*</span></label>
                <input v-model="formData.scheduled_at" type="datetime-local" class="form-input" required />
              </div>
              <div class="form-group">
                <label>Tipe</label>
                <select v-model="formData.type" class="form-input">
                  <option value="checkup">Check-up</option>
                  <option value="treatment">Perawatan</option>
                  <option value="consultation">Konsultasi</option>
                  <option value="follow_up">Follow-up</option>
                  <option value="emergency">Darurat</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Status</label>
                <select v-model="formData.status" class="form-input">
                  <option value="scheduled">Terjadwal</option>
                  <option value="confirmed">Dikonfirmasi</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Dibatalkan</option>
                  <option value="no_show">Tidak Hadir</option>
                </select>
              </div>
              <div class="form-group">
                <label>🔔 Reminder WhatsApp</label>
                <select v-model="formData.reminder_minutes" class="form-input">
                  <option :value="null">Tidak ada</option>
                  <option :value="30">30 menit sebelum</option>
                  <option :value="60">1 jam sebelum</option>
                  <option :value="120">2 jam sebelum</option>
                  <option :value="240">4 jam sebelum</option>
                  <option :value="360">6 jam sebelum</option>
                  <option :value="720">12 jam sebelum</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Durasi (menit)</label>
                <input v-model.number="formData.duration_minutes" type="number" class="form-input" placeholder="30" />
              </div>
              <div class="form-group">
                <label></label>
                <div class="wa-hint" v-if="formData.reminder_minutes">
                  <svg fill="currentColor" viewBox="0 0 24 24" width="14" height="14"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                  Reminder otomatis via WhatsApp
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Catatan</label>
              <textarea v-model="formData.notes" class="form-input form-textarea" placeholder="Catatan appointment..."></textarea>
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

    <!-- WhatsApp Reminder Modal -->
    <transition name="fade">
      <div v-if="showWaModal" class="modal-overlay" @click.self="showWaModal = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>
              <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20" style="color:#25d366;display:inline;vertical-align:middle;margin-right:8px"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.662 1.438 5.168L2 22l5.038-1.36A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.66 0-3.203-.523-4.467-1.413l-.302-.205-3.076.83.84-3.033-.213-.326A7.963 7.963 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
              Kirim WhatsApp Reminder
            </h3>
            <button class="modal-close" @click="showWaModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div v-if="selected" class="wa-preview">
              <div class="wa-preview-header">
                <strong>Ke:</strong> {{ selected.patient?.phone || '—' }} ({{ selected.patient?.full_name }})
              </div>
              <textarea v-model="reminderMsg" class="wa-message-input" rows="10"></textarea>
            </div>
            <div v-if="reminderMsg" :class="reminderMsg.includes('Error') || reminderMsg.includes('Gagal') ? 'alert alert-error' : reminderMsg.includes('✅') ? 'alert alert-success' : 'alert alert-info'" style="margin-top:0.75rem">
              {{ reminderMsg }}
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showWaModal = false">Batal</button>
            <button class="btn btn-wa" :disabled="sendingReminder" @click="executeReminder">
              <svg v-if="sendingReminder" class="btn-spinner" viewBox="0 0 24 24" fill="none" width="16" height="16">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="31.4" stroke-dashoffset="31.4"/>
              </svg>
              {{ sendingReminder ? 'Mengirim...' : 'Kirim Reminder' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* ===== Header ===== */
.appt-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.appt-title { display: flex; align-items: center; gap: 0.625rem; font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; flex-wrap: wrap; }
.appt-desc { font-size: 0.875rem; color: #64748b; margin: 0; }
.appt-actions-row { display: flex; align-items: center; gap: 0.5rem; }

/* ===== WhatsApp Badge ===== */
.wa-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.6875rem; font-weight: 600; }
.wa-connected { background: #d1fae5; color: #065f46; }
.wa-disconnected { background: #fee2e2; color: #991b1b; }

/* ===== Reminder Column ===== */
.reminder-tag { font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 6px; white-space: nowrap; }
.reminder-pending { background: #fef3c7; color: #92400e; }
.reminder-sent { background: #d1fae5; color: #065f46; }
.reminder-sent .reminder-check { margin-left: 2px; }
.reminder-none { color: #94a3b8; }

/* ===== WA button & modal ===== */
.btn-icon-wa { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 6px; background: #f0fdf4; color: #25d366; cursor: pointer; }
.btn-icon-wa:hover { background: #dcfce7; }
.btn-wa { display: inline-flex; align-items: center; gap: 0.4375rem; padding: 0.5625rem 1.125rem; border: none; border-radius: 9px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; background: #25d366; color: #fff; }
.btn-wa:hover { background: #20bd5a; }
.btn-wa:disabled { opacity: 0.6; cursor: not-allowed; }
.wa-preview { display: flex; flex-direction: column; gap: 0.5rem; }
.wa-preview-header { font-size: 0.8125rem; color: #64748b; }
.wa-message-input { width: 100%; padding: 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.8125rem; font-family: 'Cascadia Code', 'Fira Code', monospace; outline: none; resize: vertical; background: #f8fafc; }
.wa-message-input:focus { border-color: #25d366; box-shadow: 0 0 0 3px rgba(37,211,102,0.1); }
.wa-hint { display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; color: #25d366; font-weight: 500; }

/* ===== Stats ===== */
.appt-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.125rem; border-radius: 14px; background: #fff; border: 1px solid #e5e7eb; }
.stat-icon { display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 10px; }
.stat-total .stat-icon { background: #f1f5f9; color: #475569; }
.stat-scheduled .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-completed .stat-icon { background: #d1fae5; color: #059669; }
.stat-cancelled .stat-icon { background: #fee2e2; color: #dc2626; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.375rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.stat-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

/* ===== Filters ===== */
.appt-filters { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; margin-bottom: 1rem; padding: 1rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.6875rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
.filter-input { padding: 0.5rem 0.625rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.8125rem; font-family: inherit; outline: none; background: #fff; min-width: 140px; }
.filter-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.filter-search { flex: 1; min-width: 180px; }
.filter-actions { margin-left: auto; }

/* ===== Toolbar ===== */
.appt-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.view-toggle { display: flex; background: #f1f5f9; border-radius: 8px; overflow: hidden; }
.toggle-btn { display: flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border: none; background: transparent; color: #64748b; font-size: 0.8125rem; font-weight: 600; cursor: pointer; font-family: inherit; }
.toggle-btn.active { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.result-count { font-size: 0.75rem; color: #94a3b8; }

/* ===== Table ===== */
.appt-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow-x: auto; }
.appt-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.appt-table th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; font-size: 0.6875rem; padding: 0.75rem 0.875rem; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
.appt-table td { padding: 0.75rem 0.875rem; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.appt-table tr:hover td { background: #f8fafc; }
.appt-empty { text-align: center; padding: 2rem; color: #94a3b8; }

/* ===== Patient cell ===== */
.appt-patient { display: flex; align-items: center; gap: 0.5rem; }
.appt-patient-avatar { width: 30px; height: 30px; border-radius: 50%; background: #0ea5e9; color: #fff; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.appt-patient-name { font-weight: 600; color: #0f172a; }

/* ===== DateTime cell ===== */
.appt-datetime { display: flex; flex-direction: column; }
.appt-date { font-weight: 600; color: #334155; }
.appt-time { font-size: 0.6875rem; color: #94a3b8; }

/* ===== Type tag ===== */
.type-tag { font-size: 0.6875rem; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 0.1875rem 0.5rem; border-radius: 6px; }

/* ===== Badges ===== */
.badge { display: inline-block; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; white-space: nowrap; }
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-cyan { background: #cffafe; color: #155e75; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-orange { background: #ffedd5; color: #9a3412; }
.badge-gray { background: #f1f5f9; color: #475569; }

/* ===== Actions ===== */
.appt-actions { display: flex; align-items: center; gap: 0.375rem; }
.btn-icon { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 6px; background: #f1f5f9; color: #475569; cursor: pointer; }
.btn-icon:hover { background: #e2e8f0; }
.btn-icon-danger { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 6px; background: #fef2f2; color: #ef4444; cursor: pointer; }
.btn-icon-danger:hover { background: #fee2e2; }
.status-select { padding: 0.25rem 0.375rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.6875rem; font-family: inherit; background: #fff; color: #334155; cursor: pointer; }

/* ===== Calendar ===== */
.appt-calendar { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; }
.cal-header { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem; }
.cal-nav { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #334155; cursor: pointer; }
.cal-nav:hover { background: #f1f5f9; }
.cal-month { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0; }
.cal-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); margin-bottom: 0.5rem; }
.cal-weekday { text-align: center; font-size: 0.6875rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 0.375rem 0; }
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.cal-day { min-height: 80px; padding: 0.375rem; border: 1px solid #f1f5f9; border-radius: 6px; background: #fff; }
.cal-day-empty { background: #fafafa; }
.cal-today { border-color: #0ea5e9; background: #f0f9ff; }
.cal-day-num { font-size: 0.75rem; font-weight: 700; color: #334155; }
.cal-today .cal-day-num { color: #0ea5e9; }
.cal-events { display: flex; flex-direction: column; gap: 2px; margin-top: 2px; }
.cal-event { display: flex; align-items: center; gap: 0.25rem; padding: 2px 4px; border-radius: 4px; font-size: 0.625rem; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-event-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.cal-event-text { overflow: hidden; text-overflow: ellipsis; }
.cal-event-scheduled { background: #dbeafe; }
.cal-event-scheduled .cal-event-dot { background: #2563eb; }
.cal-event-confirmed { background: #cffafe; }
.cal-event-confirmed .cal-event-dot { background: #0891b2; }
.cal-event-completed { background: #d1fae5; }
.cal-event-completed .cal-event-dot { background: #059669; }
.cal-event-cancelled { background: #fee2e2; }
.cal-event-cancelled .cal-event-dot { background: #dc2626; }
.cal-event-no_show { background: #ffedd5; }
.cal-event-no_show .cal-event-dot { background: #ea580c; }
.cal-more { font-size: 0.625rem; color: #94a3b8; padding: 2px 4px; cursor: pointer; }

/* ===== Detail Grid ===== */
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

/* ===== Status ===== */
.appt-status { text-align: center; padding: 3rem; color: #64748b; font-size: 0.9375rem; }

/* ===== Animations ===== */
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .appt-stats { grid-template-columns: repeat(2, 1fr); }
  .appt-filters { flex-direction: column; }
  .filter-input { min-width: 100%; }
  .form-row { grid-template-columns: 1fr; }
  .detail-grid { grid-template-columns: 1fr; }
  .appt-table { font-size: 0.75rem; }
  .appt-table th, .appt-table td { padding: 0.5rem; }
}
</style>
