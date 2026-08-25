<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import { getModuleConfig, type ModuleConfig, type FieldDef } from '@/shared/config/moduleConfig'

const route = useRoute()
const moduleKey = computed<string>(() => (route.meta.module as string) ?? '')
const cfg = computed<ModuleConfig | undefined>(() => getModuleConfig(moduleKey.value))

const data = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const searchQ = ref('')
const showModal = ref(false)
const saving = ref(false)
const formData = ref<Record<string, any>>({})
const saveMsg = ref('')
const lookupOptions = reactive<Record<string, any[]>>({})
const lookupLoading = reactive<Record<string, boolean>>({})

const userInfo = computed(() => {
  try {
    const u = JSON.parse(localStorage.getItem('auth_user') || '{}')
    return { orgId: u.organization_id, branchId: u.branch_id }
  } catch { return { orgId: '', branchId: '' } }
})

async function fetchData(): Promise<void> {
  if (!cfg.value) return
  loading.value = true
  error.value = null
  try {
    const params: Record<string, string> = {}
    if (searchQ.value) params.search = searchQ.value
    const qs = new URLSearchParams(params).toString()
    const { data: res } = await api.get<ApiResponse<any[]>>(`${cfg.value.api}${qs ? '?' + qs : ''}`)
    data.value = res.data ?? []
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data.'
  } finally {
    loading.value = false
  }
}

watch(
  () => route.meta.module,
  () => {
    searchQ.value = ''
    showModal.value = false
    fetchData()
  },
  { immediate: true },
)

async function loadLookupOptions(field: FieldDef): Promise<void> {
  if (!field.lookupEndpoint) return
  if (lookupOptions[field.key]?.length) return
  lookupLoading[field.key] = true
  try {
    const { data: res } = await api.get<ApiResponse<any[]>>(field.lookupEndpoint)
    lookupOptions[field.key] = res.data ?? []
  } catch {
    lookupOptions[field.key] = []
  } finally {
    lookupLoading[field.key] = false
  }
}

function fieldLabel(field: FieldDef, item: any): string {
  const key = field.lookupLabel ?? 'name'
  const extra = field.lookupEndpoint?.includes('patients') && item.patient_code ? ` (${item.patient_code})` : ''
  return `${item[key] ?? item.id}${extra}`
}

function openCreate(): void {
  formData.value = {}
  if (cfg.value?.autoFill?.includes('organization_id')) formData.value.organization_id = userInfo.value.orgId
  if (cfg.value?.autoFill?.includes('branch_id')) formData.value.branch_id = userInfo.value.branchId
  saveMsg.value = ''
  showModal.value = true
  for (const f of cfg.value?.fields ?? []) {
    if (f.type === 'lookup') loadLookupOptions(f)
  }
}

async function handleSave(): Promise<void> {
  if (!cfg.value) return
  saving.value = true
  saveMsg.value = ''
  try {
    await api.post(cfg.value.api, formData.value)
    saveMsg.value = 'Berhasil disimpan.'
    showModal.value = false
    await fetchData()
  } catch (e: any) {
    saveMsg.value = e?.response?.data?.message ?? e?.message ?? 'Gagal menyimpan.'
  } finally {
    saving.value = false
  }
}

async function handleDelete(id: string): Promise<void> {
  if (!cfg.value || !confirm('Hapus data ini?')) return
  try {
    await api.delete(`${cfg.value.api}/${id}`)
    data.value = data.value.filter((d: any) => d.id !== id)
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal menghapus.'
  }
}

function cellValue(row: any, key: string): any {
  const keys = key.split('.')
  let val = row
  for (const k of keys) {
    if (val == null) return ''
    val = val[k]
  }
  return val
}

function formatValue(val: any, type: string | undefined): string {
  if (val == null || val === '') return '—'
  switch (type) {
    case 'money':
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val))
    case 'date':
      return new Date(val).toLocaleDateString('id-ID')
    case 'datetime':
      return new Date(val).toLocaleString('id-ID')
    case 'boolean':
      return ['1', 'true', true].includes(val) ? '✓' : '✗'
    default: return String(val)
  }
}

function badgeClass(status: string): string {
  const map: Record<string, string> = {
    active: 'badge-green', inactive: 'badge-gray',
    scheduled: 'badge-blue', confirmed: 'badge-cyan', completed: 'badge-green', cancelled: 'badge-red', no_show: 'badge-orange',
    paid: 'badge-green', unpaid: 'badge-yellow', overdue: 'badge-red', partially_paid: 'badge-blue', void: 'badge-gray', draft: 'badge-gray',
    ordered: 'badge-blue', processing: 'badge-yellow',
    new: 'badge-blue', contacted: 'badge-cyan', follow_up: 'badge-yellow', closed: 'badge-green',
    ordered_: 'badge-blue', in_progress: 'badge-yellow',
    trial: 'badge-cyan', past_due: 'badge-yellow', grace: 'badge-orange', expired: 'badge-red',
    contract: 'badge-blue', probation: 'badge-yellow', resigned: 'badge-gray', terminated: 'badge-red',
    main: 'badge-green', branch: 'badge-blue',
  }
  return map[status] || 'badge-gray'
}

function searchData(): void {
  fetchData()
}
</script>

<template>
  <div>
    <div v-if="!cfg" class="alert alert-error">Konfigurasi modul tidak ditemukan.</div>
    <template v-else>
      <!-- Header -->
      <div class="mp-head">
        <div>
          <h1 class="mp-title">{{ cfg.label }}</h1>
          <p class="mp-desc">{{ cfg.description }}</p>
        </div>
        <button class="btn btn-primary" @click="openCreate">+ Tambah {{ cfg.label }}</button>
      </div>

      <!-- Search -->
      <div class="mp-toolbar">
        <div class="mp-search-wrap">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="mp-search-icon">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQ"
            type="text"
            :placeholder="`Cari ${cfg.label.toLowerCase()}...`"
            class="mp-search"
            @keyup.enter="searchData"
          />
        </div>
        <button class="btn btn-ghost" @click="searchData">Cari</button>
        <button class="btn btn-ghost" @click="fetchData">↻ Refresh</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="mp-status">
        <div class="spinner" style="margin:0 auto 1rem"></div>
        Memuat data...
      </div>
      <div v-else-if="error" class="alert alert-error">{{ error }}</div>

      <!-- Empty -->
      <div v-else-if="!data.length" class="mp-empty">
        <div class="mp-empty-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
        </div>
        <h3>Belum ada data {{ cfg.label }}</h3>
        <p>Klik tombol "Tambah {{ cfg.label }}" untuk membuat data baru.</p>
      </div>

      <!-- Table -->
      <div v-else class="mp-table-wrap">
        <table class="mp-table">
          <thead>
            <tr>
              <th v-for="col in cfg.columns" :key="col.key" :style="col.maxWidth ? { maxWidth: col.maxWidth } : {}">{{ col.label }}</th>
              <th style="width:80px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data" :key="row.id">
              <td v-for="col in cfg.columns" :key="col.key" :style="col.maxWidth ? { maxWidth: col.maxWidth, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } : {}">
                <span v-if="col.type === 'badge'" :class="'badge ' + badgeClass(cellValue(row, col.key))">{{ formatValue(cellValue(row, col.key), 'text') }}</span>
                <span v-else>{{ formatValue(cellValue(row, col.key), col.type) }}</span>
              </td>
              <td>
                <button class="btn btn-sm btn-danger" @click="handleDelete(row.id)" title="Hapus">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- Modal Create -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal">
          <div class="modal-head">
            <h3>Tambah {{ cfg?.label }}</h3>
            <button class="modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div v-for="f in cfg?.fields ?? []" :key="f.key" class="mp-field">
              <label :for="'f-' + f.key">{{ f.label }}<span v-if="f.required" class="mp-req">*</span></label>
              <input
                v-if="f.type === 'text' || f.type === 'email' || f.type === 'number'"
                :id="'f-' + f.key"
                v-model="formData[f.key]"
                :type="f.type === 'number' ? 'number' : f.type === 'email' ? 'email' : 'text'"
                :placeholder="f.placeholder ?? ''"
                class="mp-input"
                :required="f.required"
              />
              <select v-else-if="f.type === 'select' && f.options" :id="'f-' + f.key" v-model="formData[f.key]" class="mp-input">
                <option value="">— Pilih —</option>
                <option v-for="o in f.options" :key="o" :value="o">{{ o }}</option>
              </select>
              <select v-else-if="f.type === 'lookup'" :id="'f-' + f.key" v-model="formData[f.key]" class="mp-input" :required="f.required">
                <option value="">{{ lookupLoading[f.key] ? 'Memuat...' : '— Pilih —' }}</option>
                <option v-for="item in lookupOptions[f.key] ?? []" :key="item.id" :value="item[f.lookupValue ?? 'id']">{{ fieldLabel(f, item) }}</option>
              </select>
              <textarea v-else-if="f.type === 'textarea'" :id="'f-' + f.key" v-model="formData[f.key]" class="mp-input mp-textarea" :placeholder="f.placeholder ?? ''"></textarea>
              <input v-else-if="f.type === 'date'" :id="'f-' + f.key" v-model="formData[f.key]" type="date" class="mp-input" />
              <input v-else-if="f.type === 'datetime'" :id="'f-' + f.key" v-model="formData[f.key]" type="datetime-local" class="mp-input" />
              <input v-else :id="'f-' + f.key" v-model="formData[f.key]" type="text" class="mp-input" />
            </div>
            <div v-if="saveMsg" :class="saveMsg.includes('Gagal') ? 'alert alert-error' : 'alert alert-success'" style="margin-top:0.75rem">{{ saveMsg }}</div>
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
.mp-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.mp-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
.mp-desc { font-size: 0.875rem; color: #64748b; margin: 0; }
.mp-toolbar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.mp-search-wrap { position: relative; flex: 1; min-width: 200px; }
.mp-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.mp-search { width: 100%; padding: 0.625rem 0.625rem 0.625rem 2.5rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.875rem; font-family: inherit; outline: none; background: #fff; }
.mp-search:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }

/* Table */
.mp-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow-x: auto; }
.mp-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.mp-table th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; font-size: 0.75rem; padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
.mp-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
.mp-table tr:last-child td { border-bottom: none; }
.mp-table tr:hover td { background: #f8fafc; }

/* Badges */
.badge { display: inline-block; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; white-space: nowrap; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-gray { background: #f1f5f9; color: #475569; }
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-cyan { background: #cffafe; color: #155e75; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-orange { background: #ffedd5; color: #9a3412; }

/* Empty */
.mp-empty { text-align: center; padding: 3rem 1rem; }
.mp-empty-icon { color: #cbd5e1; margin-bottom: 1rem; }
.mp-empty h3 { font-size: 1.125rem; color: #0f172a; margin: 0 0 0.5rem; }
.mp-empty p { font-size: 0.875rem; color: #94a3b8; margin: 0; }

/* Status */
.mp-status { text-align: center; padding: 3rem; color: #64748b; font-size: 0.9375rem; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); display: flex; align-items: center; justify-content: center; z-index: 200; }
.modal { background: #fff; border-radius: 16px; width: 95%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 30px 80px rgba(2,6,23,0.25); }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-head h3 { margin: 0; font-size: 1.125rem; font-weight: 800; color: #0f172a; }
.modal-close { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; }
.modal-body { padding: 1.5rem; }
.modal-foot { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.625rem; justify-content: flex-end; }

.mp-field { margin-bottom: 1rem; }
.mp-field label { display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.mp-req { color: #ef4444; margin-left: 2px; }
.mp-input { width: 100%; padding: 0.5rem 0.625rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; font-family: inherit; outline: none; }
.mp-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.mp-textarea { min-height: 80px; resize: vertical; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 0.4375rem; padding: 0.5625rem 1.125rem; border: none; border-radius: 9px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
.btn-primary { background: linear-gradient(135deg, #0ea5e9, #14b8a6); color: #fff; box-shadow: 0 6px 16px rgba(14,165,233,0.3); }
.btn-primary:hover { transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; transform: none; }
.btn-ghost { background: transparent; color: #64748b; border: 1.5px solid #e2e8f0; }
.btn-ghost:hover { border-color: #0ea5e9; color: #0ea5e9; }
.btn-sm { padding: 0.375rem 0.5rem; }
.btn-danger { color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; }
.btn-danger:hover { background: #fee2e2; }

.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 640px) {
  .mp-head { flex-direction: column; }
  .mp-title { font-size: 1.25rem; }
}
</style>