<script setup lang="ts">
import { onMounted } from 'vue'
import { subscriptionApi } from '@/modules/subscription/api/subscriptionApi'
import { useApi } from '@/shared/composables/useApi'
import type { SubscriptionResource } from '@/shared/types/subscription'
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const { data: sub, loading, error, refresh } = useApi<SubscriptionResource>(() => subscriptionApi.get())
onMounted(refresh)

const bannerInfo = computed(() => {
  if (!sub.value) return null
  const s = sub.value
  if (s.status === 'trial' && s.trial) return { type: 'info', text: `Trial ends in ${s.trial.days_remaining} days` }
  if (s.status === 'past_due') return { type: 'warning', text: 'Payment failed — update your payment method' }
  if (s.status === 'grace' && s.billing.grace_ends_at) return { type: 'warning', text: `Payment overdue — grace period active` }
  if (s.status === 'expired') return { type: 'error', text: 'Subscription expired — reactivate to restore access' }
  if (s.status === 'cancelled') return { type: 'error', text: 'Subscription cancelled' }
  return null
})
</script>

<template>
  <div class="p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gradient-medical mb-2">Dashboard</h1>
      <p class="text-gray-600">Welcome back to your dental practice management system</p>
    </div>

    <!-- Subscription Banner -->
    <div v-if="bannerInfo" :class="{
      'alert-info': bannerInfo?.type === 'info',
      'alert-warning': bannerInfo?.type === 'warning',
      'alert-error': bannerInfo?.type === 'error',
    }" class="alert flex justify-between items-center">
      <span>{{ bannerInfo?.text }}</span>
      <button v-if="sub?.status !== 'active' && sub?.status !== 'trial'" @click="router.push('/subscription')" class="btn-secondary text-sm py-1 px-4">View Subscription</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-500">Loading...</p>
    </div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <!-- Subscription Card -->
    <div v-else-if="sub" class="card-medical hover-lift">
      <div class="flex justify-between items-start mb-6">
        <div>
          <h2 class="text-2xl font-bold text-primary mb-1">{{ sub.plan.charAt(0).toUpperCase() + sub.plan.slice(1) }} Plan</h2>
          <p class="text-xl font-semibold text-gray-700">Rp {{ sub.price.toLocaleString('id-ID') }}<span class="text-sm font-normal text-gray-500">/bulan</span></p>
        </div>
        <span class="badge" :class="{
          'badge-primary': sub.status === 'trial',
          'badge-success': sub.status === 'active',
          'badge-warning': sub.status === 'past_due' || sub.status === 'grace',
          'badge-error': sub.status === 'expired' || sub.status === 'cancelled',
        }">{{ sub.status_label }}</span>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-6 pt-6 border-t-2 border-gray-100">
        <div v-if="sub.is_trial && sub.trial" class="text-center">
          <div class="text-3xl font-bold text-primary mb-1">{{ sub.trial.days_remaining }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Hari Trial</div>
        </div>
        <div v-if="sub.billing.next_billing_at" class="text-center">
          <div class="text-sm font-bold text-gray-800 mb-1">{{ new Date(sub.billing.next_billing_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Next Billing</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-secondary mb-1">{{ sub.storage.used_gb }}/{{ sub.storage.limit_gb }} GB</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Storage</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-accent mb-1">∞</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Users</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-accent mb-1">∞</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Records</div>
        </div>
      </div>
    </div>

    <!-- No subscription -->
    <div v-else class="alert alert-warning text-center">
      <div>
        <p class="text-lg font-semibold mb-2">No active subscription</p>
        <router-link to="/subscription" class="btn-primary inline-block">View Plans</router-link>
      </div>
    </div>
  </div>
</template>