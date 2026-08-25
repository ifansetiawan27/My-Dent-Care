<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface RadiologyRecord {
  id: string
  patient_name: string
  doctor_name: string
  examination_type: string
  image_url: string
  notes: string
  status: string
  created_at: string
}

const emptyForm = (): RadiologyRecord => ({
  id: '', patient_name: '', doctor_name: '', examination_type: '',
  image_url: '', notes: '', status: 'ordered', created_at: new Date().toISOString(),
})

const data = ref<RadiologyRecord[]>([])
const search = ref('')
const showModal = ref(false)
const form = ref<RadiologyRecord>(emptyForm())
const editing = ref(false)
const saveMsg = ref('')

function load(): void {
  try { data.value = JSON.parse(localStorage.getItem('radiology_data') || '[]') } catch { data.value = [] }
}
function save(): void { localStorage.setItem('radiology_data', JSON.stringify(data.value)) }

onMounted(load)

const filtered = computed(() => {
  if (!search.value) return data.value
  const q = search.value.toLowerCase()
  return data.value.filter(d => 
    d.patient_name.toLowerCase().includes(q) ||
    d.examination_type.toLowerCase().includes(q) ||
    d.status.includes(q)
  )
})

function openCreate(): void {
  form.value = { ...emptyForm(), id: crypto.randomUUID() }
  editing.value = false
  showModal.value = true
  saveMsg.value = ''
}

function openEdit(item: RadiologyRecord): void {
  form.value = { ...item }
  editing.value = true
  showModal.value = true
  saveMsg.value = ''
}

function handleSave(): void {
  if (!form.value.patient_name || !form.value.examination_type) { saveMsg.value = 'Nama pasien & jenis pemeriksaan wajib diisi.'; return }
  if (editing.value) {
    const idx = data.value.findIndex(d => d.id === form.value.id)
    if (idx >= 0) data.value[idx] = { ...form.value }
  } else {
    data.value.unshift({ ...form.value })
  }
  save(); showModal.value = false; saveMsg.value = ''
}

function handleDelete(id: string): void {
  if (!confirm('Hapus data radiologi ini?')) return
  data.value = data.value.filter(d => d.id !== id)
  save()
}
</script>

<template>
  <div>
    <div class="mp-head">
      <div>
        <h1 class="mp-title">Radiologi</h1>
        <p class="mp-desc">Manajemen foto X-ray & citra radiologi yang terhubung ke rekam medis.</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Tambah Pemeriksaan</button>
    </div>
    <div class="mp-toolbar">
      <div class="mp-search-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="mp-search-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input v-model="search" type="text" placeholder="Cari pasien, jenis, status..." class="mp-search" />
      </div>
    </div>
    <div v-if="!filtered.length" class="mp-empty">
      <div class="mp-empty-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg></div>
      <h3>Belum ada data radiologi</h3>
      <p>Klik "Tambah Pemeriksaan" untuk menambahkan data baru.</p>
    </div>
    <div v-else class="mp-table-wrap">
      <table class="mp-table">
        <thead><tr><th>Pasien</th><th>Dokter</th><th>Jenis</th><th>Status</th><th>Tanggal</th><th style="width:100px">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="d in filtered" :key="d.id">
            <td>{{ d.patient_name }}</td>
            <td>{{ d.doctor_name || '—' }}</td>
            <td>{{ d.examination_type }}</td>
            <td><span :class="'badge ' + (d.status === 'completed' ? 'badge-green' : d.status === 'processing' ? 'badge-yellow' : d.status === 'abnormal' ? 'badge-red' : 'badge-blue')">{{ d.status }}</span></td>
            <td>{{ new Date(d.created_at).toLocaleDateString('id-ID') }}</td>
            <td>
              <button class="btn btn-sm btn-ghost" @click="openEdit(d)" title="Edit">✎</button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(d.id)" title="Hapus"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal">
          <div class="modal-head"><h3>{{ editing ? 'Edit' : 'Tambah' }} Pemeriksaan Radiologi</h3><button class="modal-close" @click="showModal = false">&times;</button></div>
          <div class="modal-body">
            <div class="mp-field"><label>Nama Pasien <span class="mp-req">*</span></label><input v-model="form.patient_name" class="mp-input" /></div>
            <div class="mp-field"><label>Dokter</label><input v-model="form.doctor_name" class="mp-input" /></div>
            <div class="mp-field"><label>Jenis Pemeriksaan <span class="mp-req">*</span></label><select v-model="form.examination_type" class="mp-input"><option value="">— Pilih —</option><option>Panoramic</option><option>Cephalometric</option><option>CBCT</option><option>Periapical</option><option>Bitewing</option><option>Occlusal</option></select></div>
            <div class="mp-field"><label>Status</label><select v-model="form.status" class="mp-input"><option>ordered</option><option>processing</option><option>completed</option><option>abnormal</option></select></div>
            <div class="mp-field"><label>URL Gambar</label><input v-model="form.image_url" class="mp-input" placeholder="http://..." /></div>
            <div class="mp-field"><label>Catatan</label><textarea v-model="form.notes" class="mp-input mp-textarea"></textarea></div>
            <div v-if="saveMsg" :class="saveMsg.includes('wajib') ? 'alert alert-error' : 'alert alert-success'">{{ saveMsg }}</div>
          </div>
          <div class="modal-foot"><button class="btn btn-ghost" @click="showModal = false">Batal</button><button class="btn btn-primary" @click="handleSave">Simpan</button></div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* reuse ModulePage styles — they're available globally */
</style>