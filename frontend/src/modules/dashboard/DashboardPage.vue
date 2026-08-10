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
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Subscription Banner -->
    <div v-if="bannerInfo" :class="{
      'bg-blue-50 border-blue-400 text-blue-800': bannerInfo?.type === 'info',
      'bg-yellow-50 border-yellow-400 text-yellow-800': bannerInfo?.type === 'warning',
      'bg-red-50 border-red-400 text-red-800': bannerInfo?.type === 'error',
    }" class="border-l-4 p-4 rounded mb-6 flex justify-between items-center">
      <span>{{ bannerInfo?.text }}</span>
      <button v-if="sub?.status !== 'active' && sub?.status !== 'trial'" @click="router.push('/subscription')" class="text-sm font-medium underline">View Subscription</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
    <div v-else-if="error" class="bg-red-50 p-4 rounded text-red-600">{{ error }}</div>

    <!-- Subscription Card -->
    <div v-else-if="sub" class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h2 class="text-lg font-semibold">{{ sub.plan.charAt(0).toUpperCase() + sub.plan.slice(1) }} Plan</h2>
          <p class="text-gray-500">Rp {{ sub.price.toLocaleString('id-ID') }}/month</p>
        </div>
        <span :class="{
          'bg-green-100 text-green-800': sub.status === 'active',
          'bg-blue-100 text-blue-800': sub.status === 'trial',
          'bg-yellow-100 text-yellow-800': sub.status === 'past_due' || sub.status === 'grace',
          'bg-red-100 text-red-800': sub.status === 'expired' || sub.status === 'cancelled',
        }" class="px-3 py-1 rounded-full text-sm font-medium">{{ sub.status_label }}</span>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div v-if="sub.is_trial && sub.trial"><span class="text-gray-500">Trial ends</span><br><strong>{{ new Date(sub.trial.end_date).toLocaleDateString('id-ID') }}</strong></div>
        <div v-if="sub.billing.next_billing_at"><span class="text-gray-500">Next billing</span><br><strong>{{ new Date(sub.billing.next_billing_at).toLocaleDateString('id-ID') }}</strong></div>
        <div><span class="text-gray-500">Storage</span><br><strong>{{ sub.storage.used_gb }} / {{ sub.storage.limit_gb }} GB</strong></div>
        <div><span class="text-gray-500">Users</span><br><strong>Unlimited</strong></div>
        <div><span class="text-gray-500">Clinical Records</span><br><strong>Unlimited</strong></div>
      </div>
    </div>

    <!-- No subscription -->
    <div v-else class="bg-yellow-50 p-6 rounded text-center">
      <p class="text-lg">No active subscription</p>
      <router-link to="/subscription" class="text-blue-600 font-medium underline mt-2 inline-block">View Plans</router-link>
    </div>
  </div>
</template>