<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import OdontogramChart, { type ToothMark } from '@/shared/components/OdontogramChart.vue'

const data = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const showModal = ref(false)
const saving = ref(false)
const saveMsg = ref('')

const patients = ref<any[]>([])
const patientsLoading = ref(false)
const patientId = ref('')
const notes = ref('')
const toothMarks = ref<Record<string, ToothMark>>({})
const imageDataUrl = ref('')
const imageName = ref('')
const fileInput = ref<HTMLInputElement | null>(null)

const orgId = (() => {
  try { return JSON.parse(localStorage.getItem('auth_user') || '{}').organization_id ?? '' } catch { return '' }
})()

function onFileChange(e: Event): void {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  const allowed = ['image/jpeg', 'image/png']
  if (!allowed.includes(file.type)) {
    saveMsg.value = 'Format harus JPG atau PNG.'
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    saveMsg.value = 'Ukuran gambar maksimal 2MB.'
    return
  }
  imageName.value = file.name
  const reader = new FileReader()
  reader.onload = () => { imageDataUrl.value = reader.result as string; saveMsg.value = '' }
  reader.readAsDataURL(file)
}

function clearImage(): void {
  imageDataUrl.value = ''
  imageName.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

async function fetchData(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const { data: res } = await api.get<ApiResponse<any[]>>('/v1/odontograms')
    data.value = res.data ?? []
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data.'
  } finally {
    loading.value = false
  }
}

async function loadPatients(): Promise<void> {
  if (patients.value.length) return
  patientsLoading.value = true
  try {
    const { data: res } = await api.get<ApiResponse<any[]>>('/v1/patients')
    patients.value = res.data ?? []
  } catch { patients.value = [] } finally { patientsLoading.value = false }
}

onMounted(fetchData)

function openCreate(): void {
  patientId.value = ''
  notes.value = ''
  toothMarks.value = {}
  imageDataUrl.value = ''
  imageName.value = ''
  saveMsg.value = ''
  showModal.value = true
  loadPatients()
}

async function handleSave(): Promise<void> {
  if (!patientId.value) { saveMsg.value = 'Pilih pasien terlebih dahulu.'; return }
  const marks = Object.entries(toothMarks.value).filter(([, m]) => m.condition && m.condition !== 'sound')
  if (!marks.length) { saveMsg.value = 'Tandai minimal satu gigi pada odontogram.'; return }

  saving.value = true
  saveMsg.value = ''
  try {
    let created = 0
    for (const [toothNumber, mark] of marks) {
      const findings: any[] = [{ condition: mark.condition }]
      if (imageDataUrl.value) findings.push({ type: 'image', name: imageName.value, data: imageDataUrl.value })
      await api.post('/v1/odontograms', {
        organization_id: orgId,
        patient_id: patientId.value,
        tooth_number: toothNumber,
        condition: mark.condition,
        notes: notes.value,
        findings,
      })
      created++
    }
    saveMsg.value = `${created} gigi berhasil disimpan.`
    showModal.value = false
    await fetchData()
  } catch (e: any) {
    saveMsg.value = e?.response?.data?.message ?? e?.message ?? 'Gagal menyimpan.'
  } finally {
    saving.value = false
  }
}

async function handleDelete(id: string): Promise<void> {
  if (!confirm('Hapus data odontogram ini?')) return
  try {
    await api.delete(`/v1/odontograms/${id}`)
    data.value = data.value.filter((d: any) => d.id !== id)
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal menghapus.'
  }
}

function patientLabel(p: any): string {
  return p.patient_code ? `${p.full_name} (${p.patient_code})` : (p.full_name ?? p.id)
}
</script>

<template>
  <div>
    <div class="mp-head">
      <div>
        <h1 class="mp-title">Odontogram</h1>
        <p class="mp-desc">Tooth charting digital — klik gigi untuk menandai kondisi sebagai acuan report pasien.</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Tambah Odontogram</button>
    </div>

    <div v-if="loading" class="mp-status"><div class="spinner" style="margin:0 auto 1rem"></div>Memuat data...</div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>
    <div v-else-if="!data.length" class="mp-empty">
      <div class="mp-empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
      </div>
      <h3>Belum ada data odontogram</h3>
      <p>Klik "Tambah Odontogram" untuk membuat charting gigi baru.</p>
    </div>
    <div v-else class="mp-table-wrap">
      <table class="mp-table">
        <thead><tr><th>Gigi</th><th>Kondisi</th><th>Permukaan</th><th>Catatan</th><th style="width:80px">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="d in data" :key="d.id">
            <td><strong>#{{ d.tooth_number }}</strong></td>
            <td><span class="badge" :class="d.condition === 'caries' ? 'badge-red' : d.condition === 'filling' ? 'badge-blue' : d.condition === 'missing' ? 'badge-gray' : 'badge-yellow'">{{ d.condition || '—' }}</span></td>
            <td>{{ d.surface || '—' }}</td>
            <td>{{ d.notes || '—' }}</td>
            <td><button class="btn btn-sm btn-danger" @click="handleDelete(d.id)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal odonto-modal">
          <div class="modal-head">
            <h3>Tambah Odontogram</h3>
            <button class="modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div class="mp-field">
              <label>Pasien <span class="mp-req">*</span></label>
              <select v-model="patientId" class="mp-input">
                <option value="">{{ patientsLoading ? 'Memuat...' : '— Pilih Pasien —' }}</option>
                <option v-for="p in patients" :key="p.id" :value="p.id">{{ patientLabel(p) }}</option>
              </select>
            </div>

            <div class="mp-field">
              <label>Odontogram 3D Interaktif <span class="mp-req">*</span></label>
              <OdontogramChart v-model="toothMarks" />
            </div>

            <div class="mp-field">
              <label>Upload Gambar (JPG / PNG)</label>
              <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mp-upload" @change="onFileChange" />
              <div v-if="imageDataUrl" class="mp-upload-preview">
                <img :src="imageDataUrl" alt="Preview" />
                <div class="mp-upload-meta">
                  <span>{{ imageName }}</span>
                  <button type="button" class="btn btn-sm btn-danger" @click="clearImage">✕ Hapus</button>
                </div>
              </div>
            </div>

            <div class="mp-field">
              <label>Catatan</label>
              <textarea v-model="notes" class="mp-input mp-textarea" placeholder="Catatan tambahan untuk report pasien..."></textarea>
            </div>

            <div v-if="saveMsg" :class="saveMsg.includes('berhasil') ? 'alert alert-success' : 'alert alert-error'" style="margin-top:0.5rem">{{ saveMsg }}</div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showModal = false">Batal</button>
            <button class="btn btn-primary" :disabled="saving" @click="handleSave">{{ saving ? 'Menyimpan...' : 'Simpan Odontogram' }}</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.odonto-modal { max-width: 820px; }
.mp-upload { font-size: 0.875rem; font-family: inherit; }
.mp-upload-preview { margin-top: 0.625rem; border: 1px solid #e6ebf0; border-radius: 10px; overflow: hidden; }
.mp-upload-preview img { width: 100%; max-height: 220px; object-fit: contain; display: block; background: #f5f5f5; }
.mp-upload-meta { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: #fafafa; font-size: 0.8125rem; color: #595959; }
</style>