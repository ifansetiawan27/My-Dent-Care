<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { subscriptionApi } from '@/modules/subscription/api/subscriptionApi'
import { useApi } from '@/shared/composables/useApi'
import type { SubscriptionResource } from '@/shared/types/subscription'

const router = useRouter()
const { data: sub, loading, error, refresh } = useApi<SubscriptionResource>(() => subscriptionApi.get())
onMounted(refresh)

const bannerInfo = computed(() => {
  if (!sub.value) return null
  const s = sub.value
  if (s.status === 'trial' && s.trial) return { type: 'info', text: `Trial ends in ${s.trial.days_remaining} days` }
  if (s.status === 'past_due') return { type: 'warning', text: 'Payment failed — update your payment method' }
  if (s.status === 'grace' && s.billing.grace_ends_at) return { type: 'warning', text: 'Payment overdue — grace period active' }
  if (s.status === 'expired') return { type: 'error', text: 'Subscription expired — reactivate to restore access' }
  if (s.status === 'cancelled') return { type: 'error', text: 'Subscription cancelled' }
  return null
})

const planLabel = computed(() => {
  if (!sub.value) return ''
  const p = sub.value.plan
  return p.charAt(0).toUpperCase() + p.slice(1)
})

const userName = computed(() => {
  const stored = localStorage.getItem('auth_user')
  if (!stored) return 'User'
  try { return (JSON.parse(stored) as { name?: string }).name ?? 'User' } catch { return 'User' }
})

const stats = computed(() => [
  { label: 'Subscription', value: sub.value ? sub.value.status_label : '—', color: '#1890ff' },
  { label: 'Plan', value: sub.value ? planLabel.value : '—', color: '#13c2c2' },
  { label: 'Storage', value: sub.value ? `${sub.value.storage.used_gb}/${sub.value.storage.limit_gb} GB` : '—', color: '#52c41a' },
  { label: 'Users', value: '∞', color: '#faad14' },
])

const quickActions = [
  { title: 'Appointment', desc: 'Manage schedule', to: '/appointments', icon: 'calendar' },
  { title: 'Patients', desc: 'Register patient', to: '/patients', icon: 'users' },
  { title: 'Medical Records', desc: 'EMR input', to: '/emr', icon: 'file' },
  { title: 'Billing', desc: 'Create invoice', to: '/billing', icon: 'invoice' },
  { title: 'Treatment', desc: 'Treatments', to: '/treatments', icon: 'layers' },
  { title: 'Reports', desc: 'Analytics', to: '/reports', icon: 'chart' },
  { title: 'Inventory', desc: 'Stock', to: '/inventory', icon: 'box' },
  { title: 'Settings', desc: 'Clinic profile', to: '/settings', icon: 'settings' },
]
</script>

<template>
  <div class="dash">
    <div class="dash-head">
      <div>
        <h1 class="dash-title">Hi, {{ userName }} 👋</h1>
        <p class="dash-sub">Welcome back to your dental management system.</p>
      </div>
      <div class="dash-head-actions">
        <button class="btn-outlined" @click="router.push('/appointments')">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Appointment
        </button>
        <button class="btn-contained" @click="router.push('/patients')">+ Patient</button>
      </div>
    </div>

    <div v-if="bannerInfo" class="dash-alert" :class="{
      'dash-alert-info': bannerInfo?.type === 'info',
      'dash-alert-warning': bannerInfo?.type === 'warning',
      'dash-alert-error': bannerInfo?.type === 'error',
    }">
      <span>{{ bannerInfo?.text }}</span>
      <button v-if="sub?.status !== 'active' && sub?.status !== 'trial'" class="dash-alert-link" @click="router.push('/subscription')">View Subscription</button>
    </div>

    <div v-if="loading" class="dash-loading">
      <div class="spinner" style="margin:0 auto 1rem"></div>
      <p style="color:#8c8c8c">Loading...</p>
    </div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <!-- Stat cards -->
    <div class="dash-stats">
      <div v-for="s in stats" :key="s.label" class="stat-card">
        <span class="stat-dot" :style="{ background: s.color }"></span>
        <div class="stat-meta">
          <span class="stat-label">{{ s.label }}</span>
          <span class="stat-value">{{ s.value }}</span>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="dash-section">
      <h2 class="dash-section-title">Quick Actions</h2>
      <div class="dash-actions">
        <button v-for="a in quickActions" :key="a.title" class="action-card" @click="router.push(a.to)">
          <span class="action-icon">
            <svg v-if="a.icon === 'calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <svg v-else-if="a.icon === 'users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <svg v-else-if="a.icon === 'file'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <svg v-else-if="a.icon === 'invoice'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
            <svg v-else-if="a.icon === 'layers'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4" /></svg>
            <svg v-else-if="a.icon === 'chart'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <svg v-else-if="a.icon === 'box'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
          </span>
          <span class="action-text">
            <strong>{{ a.title }}</strong>
            <span>{{ a.desc }}</span>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dash { max-width: 1200px; }
.dash-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.dash-title { font-size: 1.5rem; font-weight: 700; color: #262626; margin: 0 0 0.25rem; }
.dash-sub { font-size: 0.875rem; color: #8c8c8c; margin: 0; }
.dash-head-actions { display: flex; gap: 0.75rem; }
.btn-contained { display: inline-flex; align-items: center; gap: 0.375rem; background: #1890ff; color: #fff; border: none; border-radius: 6px; padding: 0.5625rem 1.125rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
.btn-contained:hover { background: #40a9ff; }
.btn-outlined { display: inline-flex; align-items: center; gap: 0.375rem; background: #fff; color: #1890ff; border: 1px solid #1890ff; border-radius: 6px; padding: 0.5625rem 1.125rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
.btn-outlined:hover { background: #e6f7ff; }

.dash-alert { display: flex; align-items: center; gap: 0.625rem; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem; }
.dash-alert-link { margin-left: auto; background: none; border: none; font-size: 0.875rem; font-weight: 700; color: inherit; cursor: pointer; text-decoration: underline; font-family: inherit; }
.dash-alert-info { background: #e6f7ff; color: #096dd9; border: 1px solid #bae7ff; }
.dash-alert-warning { background: #fffbe6; color: #d48806; border: 1px solid #ffe58f; }
.dash-alert-error { background: #fff1f0; color: #cf1322; border: 1px solid #ffa39e; }

.dash-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border: 1px solid #f0f0f0; border-radius: 8px; padding: 1.25rem; display: flex; align-items: center; gap: 0.875rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: box-shadow .2s; }
.stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.10); }
.stat-dot { width: 10px; height: 44px; border-radius: 5px; flex-shrink: 0; }
.stat-meta { display: flex; flex-direction: column; }
.stat-label { font-size: 0.75rem; color: #8c8c8c; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-value { font-size: 1.125rem; font-weight: 700; color: #262626; }

.dash-section { margin-bottom: 2rem; }
.dash-section-title { font-size: 1rem; font-weight: 700; color: #262626; margin: 0 0 1rem; }
.dash-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.action-card { display: flex; align-items: center; gap: 0.875rem; padding: 1.125rem; cursor: pointer; text-align: left; font-family: inherit; width: 100%; border: 1px solid #f0f0f0; background: #fff; border-radius: 8px; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.action-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.10); border-color: #d9d9d9; }
.action-icon { width: 40px; height: 40px; border-radius: 8px; background: #e6f7ff; color: #1890ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.action-text { display: flex; flex-direction: column; gap: 0.125rem; min-width: 0; }
.action-text strong { font-size: 0.875rem; color: #262626; }
.action-text span { font-size: 0.75rem; color: #bfbfbf; }

.dash-loading { text-align: center; padding: 3rem 0; }

@media (max-width: 1100px) { .dash-stats { grid-template-columns: repeat(2, 1fr); } .dash-actions { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 760px) { .dash-actions { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .dash-stats { grid-template-columns: 1fr; } .dash-actions { grid-template-columns: 1fr; } }
</style>