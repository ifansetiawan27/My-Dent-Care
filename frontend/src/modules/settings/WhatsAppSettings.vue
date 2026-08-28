<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/core/api/client'

const waStatus = ref({ status: 'disconnected', message: '' })
const qrImage = ref('')
const loading = ref(false)
const generating = ref(false)
const error = ref('')

async function checkStatus(): Promise<void> {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/v1/whatsapp/status')
    waStatus.value = data
    if (data.qr_code) {
      qrImage.value = data.qr_code
    }
  } catch {
    error.value = 'Gagal memeriksa status WhatsApp.'
  } finally {
    loading.value = false
  }
}

async function generateQR(): Promise<void> {
  generating.value = true
  error.value = ''
  qrImage.value = ''
  try {
    const { data } = await api.post('/v1/whatsapp/qr')
    if (data.qr_code) {
      qrImage.value = data.qr_code
    } else if (data.message) {
      error.value = data.message
    }
  } catch {
    error.value = 'Gagal generate QR code.'
  } finally {
    generating.value = false
  }
}

async function disconnect(): Promise<void> {
  if (!confirm('Disconnect WhatsApp session?')) return
  try {
    await api.post('/v1/whatsapp/logout')
    qrImage.value = ''
    await checkStatus()
  } catch {
    error.value = 'Gagal disconnect.'
  }
}

async function testReminder(): Promise<void> {
  try {
    const { data } = await api.post('/v1/whatsapp/test-reminder')
    if (data.status === 'success') {
      error.value = ''
      alert('✅ Test reminder berhasil dikirim!')
    } else {
      error.value = 'Test reminder gagal: ' + (data.message || 'Unknown error')
    }
  } catch {
    error.value = 'Gagal mengirim test reminder.'
  }
}

onMounted(() => { checkStatus() })
</script>

<template>
  <div class="wa-settings">
    <div class="wa-header">
      <svg fill="currentColor" viewBox="0 0 24 24" width="32" height="32" style="color:#25d366">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
        <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.662 1.438 5.168L2 22l5.038-1.36A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.66 0-3.203-.523-4.467-1.413l-.302-.205-3.076.83.84-3.033-.213-.326A7.963 7.963 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
      </svg>
      <div>
        <h3 class="wa-title">WhatsApp Gateway</h3>
        <p class="wa-desc">Connect WhatsApp untuk mengirim reminder otomatis ke pasien.</p>
      </div>
    </div>

    <!-- Status -->
    <div class="wa-card">
      <div class="wa-status-row">
        <span>Status:</span>
        <span :class="['wa-status-badge', waStatus.status === 'connected' ? 'wa-connected' : waStatus.status === 'qr_ready' ? 'wa-qr-ready' : 'wa-disconnected']">
          {{ waStatus.status === 'connected' ? '✅ Connected' : waStatus.status === 'qr_ready' ? '📱 QR Ready' : '❌ Disconnected' }}
        </span>
        <span v-if="waStatus.phone_number" class="wa-phone">({{ waStatus.phone_number }})</span>
      </div>

      <div class="wa-actions">
        <button class="btn-wa" :disabled="loading" @click="checkStatus">
          <svg v-if="loading" class="btn-spinner-inline" viewBox="0 0 24 24" fill="none" width="14" height="14">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="31.4" stroke-dashoffset="31.4"/>
          </svg>
          Refresh Status
        </button>
        <button class="btn-wa-secondary" :disabled="generating" @click="generateQR">
          {{ generating ? 'Generating...' : 'Generate QR' }}
        </button>
        <button v-if="waStatus.status === 'connected'" class="btn-wa-danger" @click="disconnect">Disconnect</button>
        <button class="btn-wa-ghost" @click="testReminder">Test Reminder</button>
      </div>

      <!-- QR Code -->
      <div v-if="qrImage" class="wa-qr-section">
        <h4>Scan QR Code dengan WhatsApp di HP Anda</h4>
        <img :src="qrImage" alt="WhatsApp QR Code" class="wa-qr-img" />
        <p class="wa-qr-hint">Buka WhatsApp → Linked Devices → Link a Device → Scan QR ini</p>
      </div>

      <!-- Error -->
      <div v-if="error" class="wa-error">
        {{ error }}
      </div>
    </div>

    <!-- Reminder Info -->
    <div class="wa-card wa-info-card">
      <h4>📋 Skema Reminder Otomatis</h4>
      <ul class="wa-info-list">
        <li>Reminder dikirim via WhatsApp ke nomor telepon pasien.</li>
        <li>Pilihan waktu: <strong>30 menit, 1 jam, 2 jam, 4 jam, 6 jam, 12 jam</strong> sebelum appointment.</li>
        <li>Scheduler berjalan setiap <strong>5 menit</strong> untuk cek appointment yang akan datang.</li>
        <li>Reminder otomatis ditandai ✓ setelah terkirim.</li>
        <li>Bisa juga kirim reminder manual via tombol <svg fill="currentColor" viewBox="0 0 24 24" width="12" height="12" style="color:#25d366;display:inline"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg> di tabel appointment.</li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.wa-settings { margin-top: 2rem; }
.wa-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.wa-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; }
.wa-desc { font-size: 0.8125rem; color: #64748b; margin: 0; }

.wa-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.wa-status-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #374151; margin-bottom: 1rem; }
.wa-status-row > span:first-child { font-weight: 600; }
.wa-status-badge { padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.wa-connected { background: #d1fae5; color: #065f46; }
.wa-qr-ready { background: #dbeafe; color: #1e40af; }
.wa-disconnected { background: #fee2e2; color: #991b1b; }
.wa-phone { font-size: 0.75rem; color: #94a3b8; }

.wa-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; }
.btn-wa { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border: none; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; background: #25d366; color: #fff; }
.btn-wa:hover { background: #20bd5a; }
.btn-wa:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-wa-secondary { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border: 1.5px solid #25d366; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; background: #fff; color: #25d366; }
.btn-wa-secondary:hover { background: #f0fdf4; }
.btn-wa-danger { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border: 1.5px solid #ef4444; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; background: #fff; color: #ef4444; }
.btn-wa-danger:hover { background: #fef2f2; }
.btn-wa-ghost { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; background: #fff; color: #64748b; }
.btn-wa-ghost:hover { border-color: #25d366; color: #25d366; }

.btn-spinner-inline { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.wa-qr-section { text-align: center; padding: 1rem 0; }
.wa-qr-section h4 { font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0 0 1rem; }
.wa-qr-img { max-width: 256px; width: 100%; border: 2px solid #e5e7eb; border-radius: 12px; }
.wa-qr-hint { font-size: 0.75rem; color: #94a3b8; margin: 0.75rem 0 0; }

.wa-error { padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; font-size: 0.8125rem; }

.wa-info-card { background: #f0fdf4; border-color: #bbf7d0; }
.wa-info-card h4 { font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0 0 0.75rem; }
.wa-info-list { list-style: none; padding: 0; margin: 0; }
.wa-info-list li { font-size: 0.8125rem; color: #374151; padding: 0.375rem 0; padding-left: 1.25rem; position: relative; }
.wa-info-list li::before { content: '•'; position: absolute; left: 0.375rem; color: #25d366; font-weight: 700; }
</style>
