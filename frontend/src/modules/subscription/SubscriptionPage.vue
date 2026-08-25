<script setup lang="ts">
import { onMounted } from 'vue'
import { subscriptionApi } from './api/subscriptionApi'
import { useApi } from '@/shared/composables/useApi'
import type { PlanResource, SubscriptionResource } from '@/shared/types/subscription'
import type { ApiResponse } from '@/shared/types/api'
import api from '@/core/api/client'
import { ref } from 'vue'

const { data: sub, loading, refresh } = useApi<SubscriptionResource>(() => subscriptionApi.get())
const plan = ref<PlanResource | null>(null)
const loadingPlan = ref(true)
const message = ref('')

onMounted(async () => {
  await refresh()
  try {
    const { data } = await api.get<ApiResponse<PlanResource[]>>('/v1/subscription/plans')
    plan.value = data.data?.[0] ?? null
  } finally { loadingPlan.value = false }
})
</script>

<template>
  <div class="sub">
    <div class="sub-head">
      <div>
        <h1 class="sub-title">Subscription</h1>
        <p class="sub-desc">Satu paket lengkap untuk semua kebutuhan klinik gigi Anda.</p>
      </div>
    </div>

    <!-- Current Plan -->
    <div v-if="sub" class="sub-current">
      <div class="sub-current-top">
        <div>
          <p class="sub-current-label">Paket Aktif</p>
          <p class="sub-current-name">{{ sub.plan.charAt(0).toUpperCase() + sub.plan.slice(1) }}</p>
          <p class="sub-current-price">Rp {{ sub.price.toLocaleString('id-ID') }}/bulan &middot; per cabang</p>
        </div>
        <span class="badge" :class="{
          'badge-blue': sub.status === 'trial',
          'badge-green': sub.status === 'active',
          'badge-yellow': sub.status === 'past_due' || sub.status === 'grace',
          'badge-red': sub.status === 'expired' || sub.status === 'cancelled',
        }">{{ sub.status_label }}</span>
      </div>
      <div v-if="sub.is_trial && sub.trial" class="sub-current-trial">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>Free Trial {{ sub.trial.days_remaining }} hari tersisa &mdash; semua fitur terbuka penuh</span>
      </div>
    </div>

    <!-- Plan Card -->
    <div v-if="loadingPlan" class="sub-loading">
      <div class="spinner" style="margin:0 auto 1rem"></div>
      <p style="color:#8c8c8c">Memuat paket...</p>
    </div>
    <div v-else-if="plan" class="sub-plan-card">
      <div class="sub-plan-side">
        <span class="sub-plan-badge">FREE TRIAL {{ plan.trial_days ?? 30 }} HARI</span>
        <h2 class="sub-plan-name">{{ plan.name }}</h2>
        <div class="sub-plan-price">
          <span class="sub-plan-cur">Rp</span>
          <span class="sub-plan-num">{{ Number(plan.price).toLocaleString('id-ID') }}</span>
          <span class="sub-plan-per">/bulan<br /><small>per cabang</small></span>
        </div>
        <p class="sub-plan-note">Setelah trial berakhir. Batalkan kapan saja — data tetap aman.</p>
        <button class="btn-contained" v-if="!sub || sub.plan !== plan.code">
          {{ sub ? 'Upgrade ke ' + plan.name : 'Mulai Free Trial' }}
        </button>
        <div v-else class="sub-plan-active">Paket Saat Ini</div>
      </div>
      <div class="sub-plan-divider"></div>
      <div class="sub-plan-includes">
        <p class="sub-plan-includes-title">Semua sudah termasuk:</p>
        <ul class="sub-plan-features">
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Full features, tanpa fitur gating</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Unlimited users, pasien &amp; transaksi</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Rekam medis, odontogram &amp; EMR digital</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Billing, invoice &amp; laporan keuangan otomatis</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Backup harian otomatis &amp; pemulihan data</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Email support, dokumentasi &amp; SLA 99,5%</span></li>
          <li><svg class="sub-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Integrasi SATUSEHAT, BPJS &amp; Midtrans (roadmap)</span></li>
        </ul>
        <p class="sub-plan-billing">
          Contoh: 1 cabang = Rp {{ Number(plan.price).toLocaleString('id-ID') }}/bulan · 3 cabang = Rp {{ (Number(plan.price) * 3).toLocaleString('id-ID') }}/bulan
        </p>
      </div>
    </div>

    <div v-if="message" class="alert alert-success" style="margin-top:1rem">{{ message }}</div>
  </div>
</template>

<style scoped>
.sub { max-width: 980px; }
.sub-head { margin-bottom: 1.5rem; }
.sub-title { font-size: 1.5rem; font-weight: 700; color: #262626; margin: 0 0 0.25rem; }
.sub-desc { font-size: 0.875rem; color: #8c8c8c; margin: 0; }

.sub-current { background: #fff; border: 1px solid #f0f0f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; }
.sub-current-top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; }
.sub-current-label { font-size: 0.75rem; color: #8c8c8c; text-transform: uppercase; letter-spacing: 0.04em; }
.sub-current-name { font-size: 1.125rem; font-weight: 700; color: #262626; margin: 0.25rem 0; }
.sub-current-price { font-size: 0.875rem; color: #8c8c8c; margin: 0; }
.sub-current-trial { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.875rem; padding: 0.625rem 0.875rem; background: #e6f7ff; border-radius: 8px; color: #096dd9; font-size: 0.875rem; }

.sub-loading { text-align: center; padding: 3rem; }

.sub-plan-card { display: flex; background: #fff; border: 2px solid #bae7ff; border-radius: 16px; box-shadow: 0 8px 30px rgba(24, 144, 255, 0.10); overflow: hidden; }
.sub-plan-side { flex: 0 0 300px; background: linear-gradient(160deg, #1890ff 0%, #096dd9 100%); padding: 2rem; display: flex; flex-direction: column; gap: 0.75rem; }
.sub-plan-badge { align-self: flex-start; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.45); color: #fff; font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.07em; padding: 4px 14px; border-radius: 999px; }
.sub-plan-name { font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0; }
.sub-plan-price { display: flex; align-items: flex-end; gap: 6px; margin: 0.25rem 0; }
.sub-plan-cur { font-size: 1rem; font-weight: 700; color: rgba(255,255,255,0.85); padding-bottom: 6px; }
.sub-plan-num { font-size: 3.25rem; font-weight: 900; color: #fff; line-height: 1; }
.sub-plan-per { font-size: 0.9375rem; color: rgba(255,255,255,0.9); font-weight: 600; padding-bottom: 6px; }
.sub-plan-per small { font-size: 0.7rem; font-weight: 500; opacity: 0.8; display: block; }
.sub-plan-note { font-size: 0.8125rem; color: rgba(255,255,255,0.75); line-height: 1.45; margin: 0; }

.btn-contained { display: inline-flex; align-items: center; justify-content: center; width: 100%; background: #fff; color: #1890ff; border: none; border-radius: 8px; padding: 0.6875rem; font-size: 0.9375rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .2s; margin-top: 0.5rem; }
.btn-contained:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.sub-plan-active { background: rgba(255,255,255,0.22); color: #fff; text-align: center; padding: 0.6875rem; border-radius: 8px; font-weight: 700; font-size: 0.9375rem; margin-top: 0.5rem; }

.sub-plan-divider { width: 1px; background: #f0f0f0; flex-shrink: 0; }
.sub-plan-includes { flex: 1; padding: 2rem; }
.sub-plan-includes-title { font-size: 0.75rem; font-weight: 700; color: #bfbfbf; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 1.25rem; }
.sub-plan-features { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.25rem; }
.sub-plan-features li { display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.875rem; color: #595959; line-height: 1.4; }
.sub-check { color: #52c41a; flex-shrink: 0; margin-top: 2px; }
.sub-plan-billing { margin: 1.5rem 0 0; padding: 0.75rem 1rem; background: #e6f7ff; border: 1px solid #bae7ff; border-radius: 8px; font-size: 0.8125rem; color: #096dd9; }

.badge { display: inline-block; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; white-space: nowrap; }
.badge-blue { background: #e6f7ff; color: #096dd9; }
.badge-green { background: #f6ffed; color: #389e0d; }
.badge-yellow { background: #fffbe6; color: #d48806; }
.badge-red { background: #fff1f0; color: #cf1322; }

@media (max-width: 768px) {
  .sub-plan-card { flex-direction: column; }
  .sub-plan-divider { width: 100%; height: 1px; }
  .sub-plan-side { padding: 1.5rem; }
  .sub-plan-includes { padding: 1.5rem; }
  .sub-plan-features { grid-template-columns: 1fr; }
}
</style>