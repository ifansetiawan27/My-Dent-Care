<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'

/* ===== Types ===== */
interface VitalSigns {
  blood_pressure?: string
  pulse?: string | number
  temperature?: string | number
  respiratory_rate?: string | number
  weight?: string | number
  height?: string | number
  spo2?: string | number
}
interface Patient {
  id: string
  patient_code: string
  full_name: string
  birth_date?: string | null
  gender?: string | null
  blood_type?: string | null
  religion?: string | null
  marital_status?: string | null
  phone?: string | null
  email?: string | null
  address?: string | null
}
interface Doctor { id: string; full_name: string; doctor_code?: string }
interface Emr {
  id: string
  patient_id: string
  doctor_id?: string | null
  appointment_id?: string | null
  examination_date?: string | null
  tooth_number?: string | null
  icd_code?: string | null
  chief_complaint?: string | null
  present_illness?: string | null
  medical_history?: string | null
  allergies?: string | null
  vital_signs?: VitalSigns | null
  extra_oral_exam?: string | null
  intra_oral_exam?: string | null
  radiology_findings?: string | null
  diagnosis?: string | null
  secondary_diagnosis?: string | null
  treatment_notes?: string | null
  treatment_plan?: string | null
  prescription?: string | null
  follow_up_plan?: string | null
  status: string
  created_at?: string
  patient?: Patient
  doctor?: Doctor | null
}

/* ===== State ===== */
const emrs = ref<Emr[]>([])
const patients = ref<Patient[]>([])
const doctors = ref<Doctor[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const searchQ = ref('')
const filterStatus = ref('')
const filterPatient = ref('')

const showForm = ref(false)
const showDetail = ref(false)
const editingId = ref<string | null>(null)
const selected = ref<Emr | null>(null)
const saving = ref(false)
const saveMsg = ref('')
const form = ref<Record<string, any>>({})

const printEmr = ref<Emr | null>(null)

/* ===== Computed ===== */
const stats = computed(() => {
  const all = emrs.value
  return {
    total: all.length,
    open: all.filter(e => e.status === 'open').length,
    completed: all.filter(e => e.status === 'completed').length,
    patients: new Set(all.map(e => e.patient_id)).size,
  }
})

const filtered = computed(() => {
  let list = emrs.value
  if (filterStatus.value) list = list.filter(e => e.status === filterStatus.value)
  if (filterPatient.value) list = list.filter(e => e.patient_id === filterPatient.value)
  if (searchQ.value) {
    const q = searchQ.value.toLowerCase()
    list = list.filter(e =>
      e.patient?.full_name?.toLowerCase().includes(q) ||
      e.patient?.patient_code?.toLowerCase().includes(q) ||
      e.diagnosis?.toLowerCase().includes(q) ||
      e.icd_code?.toLowerCase().includes(q) ||
      e.doctor?.full_name?.toLowerCase().includes(q)
    )
  }
  return list
})

/* ===== Helpers ===== */
function calcAge(birth?: string | null): string {
  if (!birth) return '-'
  const b = new Date(birth)
  const now = new Date()
  let age = now.getFullYear() - b.getFullYear()
  const m = now.getMonth() - b.getMonth()
  if (m < 0 || (m === 0 && now.getDate() < b.getDate())) age--
  return `${age} th`
}
function fmtDate(d?: string | null): string {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
function fmtDateTime(d?: string | null): string {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function genderLabel(g?: string | null): string {
  if (g === 'male') return 'Laki-laki'
  if (g === 'female') return 'Perempuan'
  return '-'
}
function statusBadge(s: string): string {
  return s === 'completed' ? 'badge-success' : 'badge-warning'
}
function statusLabel(s: string): string {
  return s === 'completed' ? 'Selesai' : 'Terbuka'
}
function vit(v: VitalSigns | null | undefined, key: keyof VitalSigns, suffix = ''): string {
  const val = v?.[key]
  if (val === undefined || val === null || val === '') return '-'
  return `${val}${suffix}`
}

/* ===== Data ===== */
async function fetchData(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const { data: res } = await api.get<ApiResponse<Emr[]>>('/v1/emrs?per_page=100')
    const raw: any = res.data
    emrs.value = Array.isArray(raw) ? raw : (raw?.data ?? [])
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data rekam medis.'
  } finally {
    loading.value = false
  }
}
async function loadLookups(): Promise<void> {
  try {
    const [p, d] = await Promise.all([
      api.get<ApiResponse<Patient[]>>('/v1/patients?per_page=100'),
      api.get<ApiResponse<Doctor[]>>('/v1/doctors?per_page=100'),
    ])
    const pr: any = p.data; const dr: any = d.data
    patients.value = Array.isArray(pr) ? pr : (pr?.data ?? [])
    doctors.value = Array.isArray(dr) ? dr : (dr?.data ?? [])
  } catch { /* lookups optional */ }
}

/* ===== Form ===== */
function emptyForm(): Record<string, any> {
  return {
    patient_id: '', doctor_id: '', examination_date: '', tooth_number: '', icd_code: '',
    chief_complaint: '', present_illness: '', medical_history: '', allergies: '',
    vital_signs: { blood_pressure: '', pulse: '', temperature: '', respiratory_rate: '', weight: '', height: '', spo2: '' },
    extra_oral_exam: '', intra_oral_exam: '', radiology_findings: '',
    diagnosis: '', secondary_diagnosis: '',
    treatment_notes: '', treatment_plan: '', prescription: '', follow_up_plan: '',
    status: 'open',
  }
}
function openCreate(): void {
  editingId.value = null
  form.value = emptyForm()
  form.value.examination_date = new Date().toISOString().slice(0, 16)
  saveMsg.value = ''
  showForm.value = true
}
function openEdit(e: Emr): void {
  editingId.value = e.id
  form.value = {
    ...emptyForm(),
    patient_id: e.patient_id,
    doctor_id: e.doctor_id ?? '',
    examination_date: e.examination_date ? e.examination_date.slice(0, 16) : '',
    tooth_number: e.tooth_number ?? '',
    icd_code: e.icd_code ?? '',
    chief_complaint: e.chief_complaint ?? '',
    present_illness: e.present_illness ?? '',
    medical_history: e.medical_history ?? '',
    allergies: e.allergies ?? '',
    vital_signs: { ...emptyForm().vital_signs, ...(e.vital_signs ?? {}) },
    extra_oral_exam: e.extra_oral_exam ?? '',
    intra_oral_exam: e.intra_oral_exam ?? '',
    radiology_findings: e.radiology_findings ?? '',
    diagnosis: e.diagnosis ?? '',
    secondary_diagnosis: e.secondary_diagnosis ?? '',
    treatment_notes: e.treatment_notes ?? '',
    treatment_plan: e.treatment_plan ?? '',
    prescription: e.prescription ?? '',
    follow_up_plan: e.follow_up_plan ?? '',
    status: e.status,
  }
  saveMsg.value = ''
  showForm.value = true
}
function buildPayload(): Record<string, any> {
  const f = form.value
  const vs = f.vital_signs
  const cleanVs = Object.fromEntries(Object.entries(vs).filter(([, v]) => v !== '' && v !== null && v !== undefined))
  return {
    patient_id: f.patient_id || null,
    doctor_id: f.doctor_id || null,
    examination_date: f.examination_date ? new Date(f.examination_date).toISOString() : null,
    tooth_number: f.tooth_number || null,
    icd_code: f.icd_code || null,
    chief_complaint: f.chief_complaint || null,
    present_illness: f.present_illness || null,
    medical_history: f.medical_history || null,
    allergies: f.allergies || null,
    vital_signs: Object.keys(cleanVs).length ? cleanVs : null,
    extra_oral_exam: f.extra_oral_exam || null,
    intra_oral_exam: f.intra_oral_exam || null,
    radiology_findings: f.radiology_findings || null,
    diagnosis: f.diagnosis || null,
    secondary_diagnosis: f.secondary_diagnosis || null,
    treatment_notes: f.treatment_notes || null,
    treatment_plan: f.treatment_plan || null,
    prescription: f.prescription || null,
    follow_up_plan: f.follow_up_plan || null,
    status: f.status,
  }
}
async function saveEmr(): Promise<void> {
  if (!editingId.value && !form.value.patient_id) { saveMsg.value = 'Pilih pasien terlebih dahulu.'; return }
  saving.value = true
  saveMsg.value = ''
  try {
    const userInfo = JSON.parse(localStorage.getItem('auth_user') || '{}')
    if (editingId.value) {
      await api.put(`/v1/emrs/${editingId.value}`, buildPayload())
    } else {
      await api.post('/v1/emrs', { organization_id: userInfo.organization_id, ...buildPayload() })
    }
    showForm.value = false
    await fetchData()
  } catch (e: any) {
    saveMsg.value = e?.message ?? 'Gagal menyimpan rekam medis.'
  } finally {
    saving.value = false
  }
}
async function deleteEmr(e: Emr): Promise<void> {
  if (!confirm(`Hapus rekam medis pasien ${e.patient?.full_name ?? ''}?`)) return
  try {
    await api.delete(`/v1/emrs/${e.id}`)
    await fetchData()
  } catch { error.value = 'Gagal menghapus rekam medis.' }
}
async function toggleStatus(e: Emr): Promise<void> {
  try {
    await api.patch(`/v1/emrs/${e.id}/toggle-active`)
    await fetchData()
  } catch { error.value = 'Gagal mengubah status.' }
}
function openDetail(e: Emr): void { selected.value = e; showDetail.value = true }

/* ===== PDF Export ===== */
async function exportPdf(e: Emr): Promise<void> {
  printEmr.value = e
  await nextTick()
  window.print()
}
function onAfterPrint(): void { printEmr.value = null }

onMounted(() => {
  fetchData()
  loadLookups()
  window.addEventListener('afterprint', onAfterPrint)
})
onBeforeUnmount(() => window.removeEventListener('afterprint', onAfterPrint))
</script>

<template>
  <div class="emr-page">
    <!-- Header -->
    <div class="emr-head">
      <div>
        <h1 class="emr-title">Rekam Medis (EMR)</h1>
        <p class="emr-sub">Dokumentasi klinis pasien: data demografis, anamnesis, pemeriksaan, diagnosa, dan rencana perawatan (SOAP).</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Buat Rekam Medis</button>
    </div>

    <!-- Stats -->
    <div class="emr-stats">
      <div class="stat-card"><div class="stat-num">{{ stats.total }}</div><div class="stat-label">Total Rekam Medis</div></div>
      <div class="stat-card"><div class="stat-num warn">{{ stats.open }}</div><div class="stat-label">Terbuka</div></div>
      <div class="stat-card"><div class="stat-num ok">{{ stats.completed }}</div><div class="stat-label">Selesai</div></div>
      <div class="stat-card"><div class="stat-num info">{{ stats.patients }}</div><div class="stat-label">Pasien</div></div>
    </div>

    <!-- Filters -->
    <div class="emr-filters">
      <input v-model="searchQ" class="filter-input" type="text" placeholder="Cari pasien, kode, diagnosa, ICD, dokter..." />
      <select v-model="filterPatient" class="filter-input">
        <option value="">Semua Pasien</option>
        <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.full_name }} ({{ p.patient_code }})</option>
      </select>
      <select v-model="filterStatus" class="filter-input">
        <option value="">Semua Status</option>
        <option value="open">Terbuka</option>
        <option value="completed">Selesai</option>
      </select>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <!-- Table -->
    <div class="emr-table-wrap">
      <table class="emr-table">
        <thead>
          <tr>
            <th>Pasien</th><th>Tgl Periksa</th><th>Dokter</th><th>Elemen</th>
            <th>Diagnosa</th><th>ICD</th><th>Status</th><th class="ta-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="8" class="emr-status-msg">Memuat data...</td></tr>
          <tr v-else-if="!filtered.length"><td colspan="8" class="emr-status-msg">Belum ada rekam medis.</td></tr>
          <tr v-for="e in filtered" :key="e.id">
            <td>
              <div class="cell-name">{{ e.patient?.full_name ?? '-' }}</div>
              <div class="cell-sub">{{ e.patient?.patient_code ?? '' }}</div>
            </td>
            <td>{{ fmtDate(e.examination_date ?? e.created_at) }}</td>
            <td>{{ e.doctor?.full_name ?? '-' }}</td>
            <td>{{ e.tooth_number ?? '-' }}</td>
            <td><div class="cell-diag">{{ e.diagnosis ?? '-' }}</div></td>
            <td>{{ e.icd_code ?? '-' }}</td>
            <td><span class="badge" :class="statusBadge(e.status)">{{ statusLabel(e.status) }}</span></td>
            <td class="ta-right">
              <div class="row-actions">
                <button class="btn btn-ghost btn-sm" @click="openDetail(e)">Detail</button>
                <button class="btn btn-ghost btn-sm" @click="exportPdf(e)">PDF</button>
                <button class="btn btn-ghost btn-sm" @click="openEdit(e)">Edit</button>
                <button class="btn btn-danger btn-sm" @click="deleteEmr(e)">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <transition name="fade">
      <div v-if="showDetail && selected" class="modal-overlay" @click.self="showDetail = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>Detail Rekam Medis</h3>
            <div class="modal-head-actions">
              <button class="btn btn-primary btn-sm" @click="exportPdf(selected)">Export PDF</button>
              <button class="btn btn-ghost btn-sm" @click="showDetail = false">Tutup</button>
            </div>
          </div>
          <div class="modal-body">
            <!-- Patient Info -->
            <section class="detail-section">
              <h4 class="section-title">Data Pasien</h4>
              <div class="patient-grid">
                <div class="kv"><span class="k">Nama Lengkap</span><span class="v">{{ selected.patient?.full_name ?? '-' }}</span></div>
                <div class="kv"><span class="k">No. Rekam Medis</span><span class="v">{{ selected.patient?.patient_code ?? '-' }}</span></div>
                <div class="kv"><span class="k">Tanggal Lahir / Usia</span><span class="v">{{ fmtDate(selected.patient?.birth_date) }} / {{ calcAge(selected.patient?.birth_date) }}</span></div>
                <div class="kv"><span class="k">Jenis Kelamin</span><span class="v">{{ genderLabel(selected.patient?.gender) }}</span></div>
                <div class="kv"><span class="k">Golongan Darah</span><span class="v">{{ selected.patient?.blood_type ?? '-' }}</span></div>
                <div class="kv"><span class="k">Agama</span><span class="v">{{ selected.patient?.religion ?? '-' }}</span></div>
                <div class="kv"><span class="k">Status Perkawinan</span><span class="v">{{ selected.patient?.marital_status ?? '-' }}</span></div>
                <div class="kv"><span class="k">No. Telepon</span><span class="v">{{ selected.patient?.phone ?? '-' }}</span></div>
                <div class="kv"><span class="k">Email</span><span class="v">{{ selected.patient?.email ?? '-' }}</span></div>
                <div class="kv kv-wide"><span class="k">Alamat</span><span class="v">{{ selected.patient?.address ?? '-' }}</span></div>
              </div>
            </section>

            <!-- Exam Info -->
            <section class="detail-section">
              <h4 class="section-title">Informasi Pemeriksaan</h4>
              <div class="patient-grid">
                <div class="kv"><span class="k">Tanggal Periksa</span><span class="v">{{ fmtDateTime(selected.examination_date ?? selected.created_at) }}</span></div>
                <div class="kv"><span class="k">Dokter Pemeriksa</span><span class="v">{{ selected.doctor?.full_name ?? '-' }}</span></div>
                <div class="kv"><span class="k">Elemen Gigi</span><span class="v">{{ selected.tooth_number ?? '-' }}</span></div>
                <div class="kv"><span class="k">Kode ICD-10</span><span class="v">{{ selected.icd_code ?? '-' }}</span></div>
                <div class="kv"><span class="k">Status</span><span class="v"><span class="badge" :class="statusBadge(selected.status)">{{ statusLabel(selected.status) }}</span></span></div>
              </div>
            </section>

            <!-- SOAP -->
            <section class="detail-section">
              <h4 class="section-title soap-s">S — Subjektif</h4>
              <div class="kv-block"><span class="k">Keluhan Utama</span><p class="v">{{ selected.chief_complaint ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Riwayat Penyakit Sekarang</span><p class="v">{{ selected.present_illness ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Riwayat Penyakit Sistemik</span><p class="v">{{ selected.medical_history ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Alergi</span><p class="v">{{ selected.allergies ?? '-' }}</p></div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-o">O — Objektif</h4>
              <div class="vital-grid">
                <div class="kv"><span class="k">Tekanan Darah</span><span class="v">{{ vit(selected.vital_signs, 'blood_pressure', ' mmHg') }}</span></div>
                <div class="kv"><span class="k">Nadi</span><span class="v">{{ vit(selected.vital_signs, 'pulse', ' x/mnt') }}</span></div>
                <div class="kv"><span class="k">Suhu</span><span class="v">{{ vit(selected.vital_signs, 'temperature', ' °C') }}</span></div>
                <div class="kv"><span class="k">Pernapasan</span><span class="v">{{ vit(selected.vital_signs, 'respiratory_rate', ' x/mnt') }}</span></div>
                <div class="kv"><span class="k">Berat Badan</span><span class="v">{{ vit(selected.vital_signs, 'weight', ' kg') }}</span></div>
                <div class="kv"><span class="k">Tinggi Badan</span><span class="v">{{ vit(selected.vital_signs, 'height', ' cm') }}</span></div>
                <div class="kv"><span class="k">SpO2</span><span class="v">{{ vit(selected.vital_signs, 'spo2', ' %') }}</span></div>
              </div>
              <div class="kv-block"><span class="k">Pemeriksaan Ekstra Oral</span><p class="v">{{ selected.extra_oral_exam ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Pemeriksaan Intra Oral</span><p class="v">{{ selected.intra_oral_exam ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Temuan Radiologi</span><p class="v">{{ selected.radiology_findings ?? '-' }}</p></div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-a">A — Assessment</h4>
              <div class="kv-block"><span class="k">Diagnosa Utama</span><p class="v strong">{{ selected.diagnosis ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Diagnosa Banding / Penyerta</span><p class="v">{{ selected.secondary_diagnosis ?? '-' }}</p></div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-p">P — Planning</h4>
              <div class="kv-block"><span class="k">Tindakan / Perawatan</span><p class="v">{{ selected.treatment_notes ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Rencana Perawatan</span><p class="v">{{ selected.treatment_plan ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Resep Obat</span><p class="v">{{ selected.prescription ?? '-' }}</p></div>
              <div class="kv-block"><span class="k">Instruksi & Kontrol</span><p class="v">{{ selected.follow_up_plan ?? '-' }}</p></div>
            </section>
          </div>
        </div>
      </div>
    </transition>

    <!-- Form Modal -->
    <transition name="fade">
      <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
        <div class="modal modal-lg">
          <div class="modal-head">
            <h3>{{ editingId ? 'Edit Rekam Medis' : 'Buat Rekam Medis' }}</h3>
            <button class="btn btn-ghost btn-sm" @click="showForm = false">Tutup</button>
          </div>
          <div class="modal-body">
            <div v-if="saveMsg" class="alert alert-error">{{ saveMsg }}</div>

            <section class="detail-section">
              <h4 class="section-title">Informasi Pemeriksaan</h4>
              <div class="form-row">
                <div class="form-group">
                  <label>Pasien <span class="req">*</span></label>
                  <select v-model="form.patient_id" :disabled="!!editingId">
                    <option value="">— Pilih Pasien —</option>
                    <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.full_name }} ({{ p.patient_code }})</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Dokter Pemeriksa</label>
                  <select v-model="form.doctor_id">
                    <option value="">— Pilih Dokter —</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.full_name }}</option>
                  </select>
                </div>
              </div>
              <div class="form-row form-row-4">
                <div class="form-group">
                  <label>Tanggal Periksa</label>
                  <input v-model="form.examination_date" type="datetime-local" />
                </div>
                <div class="form-group">
                  <label>Elemen Gigi</label>
                  <input v-model="form.tooth_number" type="text" placeholder="mis. 11, 26, 36-37" />
                </div>
                <div class="form-group">
                  <label>Kode ICD-10</label>
                  <input v-model="form.icd_code" type="text" placeholder="mis. K02.1" />
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select v-model="form.status">
                    <option value="open">Terbuka</option>
                    <option value="completed">Selesai</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-s">S — Subjektif</h4>
              <div class="form-group"><label>Keluhan Utama</label><textarea v-model="form.chief_complaint" rows="2"></textarea></div>
              <div class="form-group"><label>Riwayat Penyakit Sekarang</label><textarea v-model="form.present_illness" rows="2"></textarea></div>
              <div class="form-row">
                <div class="form-group"><label>Riwayat Penyakit Sistemik</label><textarea v-model="form.medical_history" rows="2"></textarea></div>
                <div class="form-group"><label>Alergi (Obat/Makanan/Bahan)</label><textarea v-model="form.allergies" rows="2"></textarea></div>
              </div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-o">O — Objektif</h4>
              <div class="form-row form-row-4">
                <div class="form-group"><label>Tekanan Darah (mmHg)</label><input v-model="form.vital_signs.blood_pressure" type="text" placeholder="120/80" /></div>
                <div class="form-group"><label>Nadi (x/mnt)</label><input v-model="form.vital_signs.pulse" type="number" /></div>
                <div class="form-group"><label>Suhu (°C)</label><input v-model="form.vital_signs.temperature" type="number" step="0.1" /></div>
                <div class="form-group"><label>Pernapasan (x/mnt)</label><input v-model="form.vital_signs.respiratory_rate" type="number" /></div>
              </div>
              <div class="form-row form-row-4">
                <div class="form-group"><label>Berat Badan (kg)</label><input v-model="form.vital_signs.weight" type="number" step="0.1" /></div>
                <div class="form-group"><label>Tinggi Badan (cm)</label><input v-model="form.vital_signs.height" type="number" step="0.1" /></div>
                <div class="form-group"><label>SpO2 (%)</label><input v-model="form.vital_signs.spo2" type="number" /></div>
                <div class="form-group"></div>
              </div>
              <div class="form-group"><label>Pemeriksaan Ekstra Oral</label><textarea v-model="form.extra_oral_exam" rows="2"></textarea></div>
              <div class="form-group"><label>Pemeriksaan Intra Oral</label><textarea v-model="form.intra_oral_exam" rows="2"></textarea></div>
              <div class="form-group"><label>Temuan Radiologi</label><textarea v-model="form.radiology_findings" rows="2"></textarea></div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-a">A — Assessment</h4>
              <div class="form-group"><label>Diagnosa Utama</label><textarea v-model="form.diagnosis" rows="2"></textarea></div>
              <div class="form-group"><label>Diagnosa Banding / Penyerta</label><textarea v-model="form.secondary_diagnosis" rows="2"></textarea></div>
            </section>

            <section class="detail-section">
              <h4 class="section-title soap-p">P — Planning</h4>
              <div class="form-group"><label>Tindakan / Perawatan</label><textarea v-model="form.treatment_notes" rows="2"></textarea></div>
              <div class="form-group"><label>Rencana Perawatan</label><textarea v-model="form.treatment_plan" rows="2"></textarea></div>
              <div class="form-row">
                <div class="form-group"><label>Resep Obat</label><textarea v-model="form.prescription" rows="2"></textarea></div>
                <div class="form-group"><label>Instruksi & Jadwal Kontrol</label><textarea v-model="form.follow_up_plan" rows="2"></textarea></div>
              </div>
            </section>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showForm = false">Batal</button>
            <button class="btn btn-primary" :disabled="saving" @click="saveEmr">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Print / PDF Document -->
    <div v-if="printEmr" class="print-document">
      <div class="pdf-header">
        <div class="pdf-clinic">
          <div class="pdf-clinic-name">MY DENT CARE</div>
          <div class="pdf-clinic-sub">Klinik Gigi — Rekam Medis Pasien</div>
        </div>
        <div class="pdf-title">REKAM MEDIS (EMR)</div>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">DATA PASIEN</div>
        <table class="pdf-kv">
          <tbody>
            <tr><td class="k">Nama Lengkap</td><td class="v">: {{ printEmr.patient?.full_name ?? '-' }}</td><td class="k">No. RM</td><td class="v">: {{ printEmr.patient?.patient_code ?? '-' }}</td></tr>
            <tr><td class="k">Tgl Lahir / Usia</td><td class="v">: {{ fmtDate(printEmr.patient?.birth_date) }} / {{ calcAge(printEmr.patient?.birth_date) }}</td><td class="k">Jenis Kelamin</td><td class="v">: {{ genderLabel(printEmr.patient?.gender) }}</td></tr>
            <tr><td class="k">Gol. Darah</td><td class="v">: {{ printEmr.patient?.blood_type ?? '-' }}</td><td class="k">Agama</td><td class="v">: {{ printEmr.patient?.religion ?? '-' }}</td></tr>
            <tr><td class="k">No. Telepon</td><td class="v">: {{ printEmr.patient?.phone ?? '-' }}</td><td class="k">Email</td><td class="v">: {{ printEmr.patient?.email ?? '-' }}</td></tr>
            <tr><td class="k">Alamat</td><td class="v" colspan="3">: {{ printEmr.patient?.address ?? '-' }}</td></tr>
          </tbody>
        </table>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">INFORMASI PEMERIKSAAN</div>
        <table class="pdf-kv">
          <tbody>
            <tr><td class="k">Tanggal Periksa</td><td class="v">: {{ fmtDateTime(printEmr.examination_date ?? printEmr.created_at) }}</td><td class="k">Dokter</td><td class="v">: {{ printEmr.doctor?.full_name ?? '-' }}</td></tr>
            <tr><td class="k">Elemen Gigi</td><td class="v">: {{ printEmr.tooth_number ?? '-' }}</td><td class="k">Kode ICD-10</td><td class="v">: {{ printEmr.icd_code ?? '-' }}</td></tr>
          </tbody>
        </table>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">S — SUBJEKTIF</div>
        <div class="pdf-line"><b>Keluhan Utama:</b> {{ printEmr.chief_complaint ?? '-' }}</div>
        <div class="pdf-line"><b>Riwayat Penyakit Sekarang:</b> {{ printEmr.present_illness ?? '-' }}</div>
        <div class="pdf-line"><b>Riwayat Penyakit Sistemik:</b> {{ printEmr.medical_history ?? '-' }}</div>
        <div class="pdf-line"><b>Alergi:</b> {{ printEmr.allergies ?? '-' }}</div>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">O — OBJEKTIF</div>
        <div class="pdf-line">
          TD: {{ vit(printEmr.vital_signs, 'blood_pressure', ' mmHg') }} |
          Nadi: {{ vit(printEmr.vital_signs, 'pulse', ' x/mnt') }} |
          Suhu: {{ vit(printEmr.vital_signs, 'temperature', ' °C') }} |
          RR: {{ vit(printEmr.vital_signs, 'respiratory_rate', ' x/mnt') }} |
          BB: {{ vit(printEmr.vital_signs, 'weight', ' kg') }} |
          TB: {{ vit(printEmr.vital_signs, 'height', ' cm') }} |
          SpO2: {{ vit(printEmr.vital_signs, 'spo2', ' %') }}
        </div>
        <div class="pdf-line"><b>Ekstra Oral:</b> {{ printEmr.extra_oral_exam ?? '-' }}</div>
        <div class="pdf-line"><b>Intra Oral:</b> {{ printEmr.intra_oral_exam ?? '-' }}</div>
        <div class="pdf-line"><b>Radiologi:</b> {{ printEmr.radiology_findings ?? '-' }}</div>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">A — ASSESSMENT</div>
        <div class="pdf-line"><b>Diagnosa Utama:</b> {{ printEmr.diagnosis ?? '-' }}</div>
        <div class="pdf-line"><b>Diagnosa Banding:</b> {{ printEmr.secondary_diagnosis ?? '-' }}</div>
      </div>

      <div class="pdf-section">
        <div class="pdf-section-title">P — PLANNING</div>
        <div class="pdf-line"><b>Tindakan:</b> {{ printEmr.treatment_notes ?? '-' }}</div>
        <div class="pdf-line"><b>Rencana Perawatan:</b> {{ printEmr.treatment_plan ?? '-' }}</div>
        <div class="pdf-line"><b>Resep Obat:</b> {{ printEmr.prescription ?? '-' }}</div>
        <div class="pdf-line"><b>Instruksi & Kontrol:</b> {{ printEmr.follow_up_plan ?? '-' }}</div>
      </div>

      <div class="pdf-sign">
        <div class="pdf-sign-col">
          <div>Pasien / Wali</div>
          <div class="pdf-sign-space"></div>
          <div>( {{ printEmr.patient?.full_name ?? '________________' }} )</div>
        </div>
        <div class="pdf-sign-col">
          <div>Dokter Pemeriksa</div>
          <div class="pdf-sign-space"></div>
          <div>( {{ printEmr.doctor?.full_name ?? '________________' }} )</div>
        </div>
      </div>

      <div class="pdf-footer">Dicetak pada {{ fmtDateTime(new Date().toISOString()) }} — My Dent Care EMR</div>
    </div>
  </div>
</template>

<style scoped>
.emr-page { padding: 0; }
.emr-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; }
.emr-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
.emr-sub { color: #64748b; font-size: 0.875rem; margin: 0; max-width: 640px; }

/* Stats */
.emr-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.875rem; margin-bottom: 1.25rem; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1rem 1.125rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
.stat-num { font-size: 1.625rem; font-weight: 800; color: #0f172a; }
.stat-num.warn { color: #d97706; } .stat-num.ok { color: #059669; } .stat-num.info { color: #0284c7; }
.stat-label { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 0.125rem; text-transform: uppercase; letter-spacing: .03em; }

/* Filters */
.emr-filters { display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.filter-input { padding: 0.5625rem 0.875rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; font-family: inherit; background: #fff; color: #0f172a; min-width: 200px; }
.filter-input:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
.filter-input:first-child { flex: 1; min-width: 260px; }

/* Alerts */
.alert { padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.75rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Table */
.emr-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: auto; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
.emr-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.emr-table th { text-align: left; padding: 0.75rem 0.875rem; background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.emr-table td { padding: 0.75rem 0.875rem; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: top; }
.emr-table tbody tr:hover { background: #f8fafc; }
.emr-table tbody tr:last-child td { border-bottom: none; }
.ta-right { text-align: right; }
.cell-name { font-weight: 600; } .cell-sub { color: #94a3b8; font-size: 0.75rem; }
.cell-diag { max-width: 220px; }
.emr-status-msg { text-align: center; padding: 3rem; color: #64748b; }
.row-actions { display: inline-flex; gap: 0.375rem; flex-wrap: wrap; justify-content: flex-end; }

/* Badges */
.badge { display: inline-block; padding: 0.1875rem 0.625rem; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; }
.badge-success { background: #ecfdf5; color: #047857; }
.badge-warning { background: #fffbeb; color: #b45309; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.4375rem; padding: 0.5625rem 1.125rem; border: none; border-radius: 9px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
.btn-primary { background: linear-gradient(135deg, #0ea5e9, #14b8a6); color: #fff; box-shadow: 0 6px 16px rgba(14,165,233,.3); }
.btn-primary:hover { transform: translateY(-1px); }
.btn-primary:disabled { opacity: .6; transform: none; }
.btn-ghost { background: transparent; color: #64748b; border: 1.5px solid #e2e8f0; }
.btn-ghost:hover { border-color: #0ea5e9; color: #0ea5e9; }
.btn-sm { padding: 0.375rem 0.625rem; font-size: 0.75rem; }
.btn-danger { color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; }
.btn-danger:hover { background: #fee2e2; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: flex-start; justify-content: center; padding: 2rem 1rem; z-index: 50; overflow: auto; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 560px; box-shadow: 0 20px 50px rgba(15,23,42,.25); }
.modal-lg { max-width: 840px; }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; }
.modal-head h3 { margin: 0; font-size: 1.0625rem; font-weight: 700; color: #0f172a; }
.modal-head-actions { display: flex; gap: 0.5rem; }
.modal-body { padding: 1.25rem; }
.modal-foot { display: flex; justify-content: flex-end; gap: 0.625rem; padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; }

/* Detail sections */
.detail-section { margin-bottom: 1.25rem; }
.section-title { font-size: 0.8125rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: .04em; margin: 0 0 0.625rem; padding-bottom: 0.375rem; border-bottom: 2px solid #e2e8f0; }
.soap-s { border-color: #fbbf24; } .soap-o { border-color: #38bdf8; } .soap-a { border-color: #f472b6; } .soap-p { border-color: #34d399; }

.patient-grid, .vital-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem 1.25rem; }
.vital-grid { grid-template-columns: repeat(3, 1fr); margin-bottom: 0.625rem; }
.kv { display: flex; flex-direction: column; gap: 0.125rem; }
.kv .k, .kv-block .k { font-size: 0.6875rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.kv .v { font-size: 0.875rem; color: #0f172a; font-weight: 500; }
.kv-wide { grid-column: 1 / -1; }
.kv-block { margin-bottom: 0.625rem; }
.kv-block .v { margin: 0.125rem 0 0; font-size: 0.875rem; color: #1e293b; white-space: pre-wrap; }
.kv-block .v.strong { font-weight: 700; }

/* Form */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem; }
.form-row-4 { grid-template-columns: repeat(4, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.75rem; font-weight: 600; color: #475569; }
.req { color: #ef4444; }
.form-group input, .form-group select, .form-group textarea { padding: 0.5625rem 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 0.875rem; font-family: inherit; color: #0f172a; background: #fff; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
.form-group textarea { resize: vertical; }
.form-group input:disabled, .form-group select:disabled { background: #f1f5f9; color: #64748b; }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== Print / PDF ===== */
.print-document { display: none; }
@media print {
  body * { visibility: hidden; }
  .print-document, .print-document * { visibility: visible; }
  .print-document { display: block; position: absolute; inset: 0; width: 100%; padding: 24px; color: #000; font-family: Georgia, 'Times New Roman', serif; font-size: 12px; }
  .pdf-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px; }
  .pdf-clinic-name { font-size: 20px; font-weight: 700; letter-spacing: .05em; }
  .pdf-clinic-sub { font-size: 11px; color: #333; }
  .pdf-title { font-size: 15px; font-weight: 700; text-transform: uppercase; }
  .pdf-section { margin-bottom: 12px; }
  .pdf-section-title { font-weight: 700; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 6px; }
  .pdf-kv { width: 100%; border-collapse: collapse; }
  .pdf-kv td { padding: 2px 4px; vertical-align: top; }
  .pdf-kv .k { width: 130px; color: #333; }
  .pdf-kv .v { font-weight: 500; }
  .pdf-line { margin: 3px 0; line-height: 1.5; }
  .pdf-sign { display: flex; justify-content: space-between; margin-top: 32px; }
  .pdf-sign-col { text-align: center; width: 220px; }
  .pdf-sign-space { height: 56px; }
  .pdf-footer { margin-top: 24px; font-size: 10px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 6px; }
}

/* Responsive */
@media (max-width: 768px) {
  .emr-stats { grid-template-columns: repeat(2, 1fr); }
  .emr-filters { flex-direction: column; }
  .filter-input { min-width: 100%; }
  .form-row, .form-row-4 { grid-template-columns: 1fr; }
  .patient-grid, .vital-grid { grid-template-columns: 1fr; }
  .emr-table { font-size: 0.75rem; }
}
</style>
