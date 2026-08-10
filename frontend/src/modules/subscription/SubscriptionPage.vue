<script setup lang="ts">
import { onMounted } from 'vue'
import { subscriptionApi } from './api/subscriptionApi'
import { useApi } from '@/shared/composables/useApi'
import type { PlanResource, SubscriptionResource } from '@/shared/types/subscription'
import type { ApiResponse } from '@/shared/types/api'
import api from '@/core/api/client'
import { ref } from 'vue'

const { data: sub, loading, refresh } = useApi<SubscriptionResource>(() => subscriptionApi.get())
const plans = ref<PlanResource[]>([])
const loadingPlans = ref(true)
const message = ref('')

onMounted(async () => {
  await refresh()
  try {
    const { data } = await api.get<ApiResponse<PlanResource[]>>('/v1/subscription/plans')
    plans.value = data.data
  } finally { loadingPlans.value = false }
})
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Subscription</h1>

    <!-- Current -->
    <div v-if="sub" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-2">Current Plan</h2>
      <p><strong>{{ sub.plan }}</strong> — Rp {{ sub.price.toLocaleString('id-ID') }}/month — {{ sub.status_label }}</p>
      <div v-if="sub.is_trial && sub.trial" class="mt-2 text-blue-600">Trial: {{ sub.trial.days_remaining }} days remaining</div>
    </div>

    <!-- Plans -->
    <h2 class="text-xl font-semibold mb-4">Available Plans</h2>
    <div v-if="loadingPlans" class="text-gray-500">Loading plans...</div>
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div v-for="p in plans" :key="p.code" class="bg-white rounded-lg shadow p-6 border-t-4" :class="{
        'border-blue-500': p.code === 'starter',
        'border-green-500': p.code === 'professional',
        'border-purple-500': p.code === 'enterprise',
      }">
        <h3 class="text-lg font-bold">{{ p.name }}</h3>
        <p class="text-2xl font-bold my-2">Rp {{ p.price.toLocaleString('id-ID') }}<span class="text-sm font-normal text-gray-500">/month</span></p>
        <ul class="text-sm space-y-1 text-gray-600 mb-4">
          <li>✓ {{ p.storage_gb }} GB Storage</li>
          <li>✓ Unlimited Users</li>
          <li>✓ Unlimited Clinical Records</li>
          <li v-if="p.branches === -1">✓ Unlimited Branches</li>
          <li v-else-if="p.branches === 1">✓ {{ p.branches }} Branch</li>
          <li v-else>✓ Up to {{ p.branches }} Branches</li>
        </ul>
        <button v-if="sub?.plan !== p.code" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">
          {{ sub ? 'Upgrade' : 'Select' }}
        </button>
        <span v-else class="block text-center text-green-600 font-medium text-sm py-2">Current Plan</span>
      </div>
    </div>

    <div v-if="message" class="mt-4 bg-green-50 p-3 rounded text-green-700">{{ message }}</div>
  </div>
</template>