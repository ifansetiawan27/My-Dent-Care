<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { settingsApi } from './api/settingsApi'
import type { ClinicSettings } from './api/settingsApi'

interface OrgForm {
  company_name: string
  legal_name: string
  email: string
  phone: string
  website: string
  address: string
  city: string
  province: string
  postal_code: string
  tax_number?: string
  timezone?: string
  currency?: string
}

const loading = ref(true)
const saving = ref(false)
const message = ref('')
const error = ref('')
const form = ref<OrgForm>({
  company_name: '', legal_name: '', email: '', phone: '', website: '',
  address: '', city: '', province: '', postal_code: '',
  tax_number: '', timezone: '', currency: '',
})

onMounted(async () => {
  loading.value = true
  try {
    const s: ClinicSettings = await settingsApi.get()
    const c = s.clinic
    form.value = {
      company_name: c.name ?? '',
      legal_name: c.legal_name ?? '',
      email: c.email ?? '',
      phone: c.phone ?? '',
      website: c.website ?? '',
      address: c.address ?? '',
      city: c.city ?? '',
      province: c.province ?? '',
      postal_code: c.postal_code ?? '',
      tax_number: '', timezone: '', currency: '',
    }
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data organisasi.'
  } finally {
    loading.value = false
  }
})

async function save(): Promise<void> {
  saving.value = true
  message.value = ''
  try {
    await settingsApi.update({ ...form.value })
    message.value = 'Data organisasi berhasil disimpan.'
  } catch (e: any) {
    message.value = 'Gagal menyimpan: ' + (e?.message ?? 'kesalahan tidak diketahui.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <div class="mp-head">
      <div>
        <h1 class="mp-title">Organisasi</h1>
        <p class="mp-desc">Profil organisasi klinik, identitas legal, dan konfigurasi multi-tenant.</p>
      </div>
    </div>

    <div v-if="loading" class="mp-status"><div class="spinner" style="margin:0 auto 1rem"></div>Memuat data...</div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>
    <div v-else>
      <!-- Info banner -->
      <div class="org-banner">
        <div class="org-banner-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="30" height="30"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M10 10h.01M14 10h.01M10 14h.01M14 14h.01" /></svg>
        </div>
        <div>
          <h3 class="org-banner-name">{{ form.company_name || 'Klinik Anda' }}</h3>
          <p class="org-banner-sub">{{ form.legal_name || 'Belum ada nama legal' }} · {{ form.city || '—' }}, {{ form.province || '—' }}</p>
        </div>
      </div>

      <div class="card-medical" style="padding:1.75rem;margin-top:1.5rem">
        <h2 style="font-size:1.125rem;font-weight:800;color:var(--color-gray-900);margin-bottom:1.5rem">Informasi Organisasi</h2>
        <div class="org-grid">
          <div class="mp-field"><label>Nama Klinik</label><input v-model="form.company_name" class="mp-input" /></div>
          <div class="mp-field"><label>Nama Legal</label><input v-model="form.legal_name" class="mp-input" /></div>
          <div class="mp-field"><label>Email</label><input v-model="form.email" type="email" class="mp-input" /></div>
          <div class="mp-field"><label>Telepon</label><input v-model="form.phone" class="mp-input" /></div>
          <div class="mp-field"><label>Website</label><input v-model="form.website" class="mp-input" /></div>
          <div class="mp-field"><label>Kota</label><input v-model="form.city" class="mp-input" /></div>
          <div class="mp-field"><label>Provinsi</label><input v-model="form.province" class="mp-input" /></div>
          <div class="mp-field"><label>Kode Pos</label><input v-model="form.postal_code" class="mp-input" /></div>
          <div class="mp-field org-span-2"><label>Alamat</label><textarea v-model="form.address" class="mp-input mp-textarea"></textarea></div>
        </div>
        <div v-if="message" :class="message.includes('berhasil') ? 'alert alert-success' : 'alert alert-error'">{{ message }}</div>
        <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.org-banner { display: flex; align-items: center; gap: 1rem; background: linear-gradient(135deg, #eff6ff, #f0fdfa); border: 1px solid #bae6fd; border-radius: 14px; padding: 1.25rem 1.5rem; }
.org-banner-icon { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #0ea5e9, #14b8a6); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.org-banner-name { font-size: 1.125rem; font-weight: 800; color: var(--color-gray-900); margin: 0; }
.org-banner-sub { font-size: 0.875rem; color: var(--color-gray-500); margin: 0.25rem 0 0; }
.org-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.25rem; }
.org-span-2 { grid-column: span 2; }
@media (max-width: 640px) { .org-grid { grid-template-columns: 1fr; } .org-span-2 { grid-column: span 1; } }
</style>