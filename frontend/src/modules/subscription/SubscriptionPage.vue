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
  <div class="p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gradient-medical mb-2">Subscription Plans</h1>
      <p class="text-gray-600">Choose the perfect plan for your dental practice</p>
    </div>

    <!-- Current Plan -->
    <div v-if="sub" class="card-medical mb-8">
      <h2 class="text-xl font-bold text-primary mb-3">Current Plan</h2>
      <div class="flex items-center justify-between">
        <div>
          <p class="text-2xl font-bold text-gray-800">{{ sub.plan.charAt(0).toUpperCase() + sub.plan.slice(1) }}</p>
          <p class="text-lg text-gray-600">Rp {{ sub.price.toLocaleString('id-ID') }}/bulan</p>
        </div>
        <span class="badge" :class="{
          'badge-primary': sub.status === 'trial',
          'badge-success': sub.status === 'active',
          'badge-warning': sub.status === 'past_due' || sub.status === 'grace',
          'badge-error': sub.status === 'expired' || sub.status === 'cancelled',
        }">{{ sub.status_label }}</span>
      </div>
      <div v-if="sub.is_trial && sub.trial" class="mt-4 alert alert-info">
        Trial: {{ sub.trial.days_remaining }} hari tersisa
      </div>
    </div>

    <!-- Plans Grid -->
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Available Plans</h2>
    <div v-if="loadingPlans" class="text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-500">Loading plans...</p>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="p in plans" :key="p.code" class="card hover-lift relative" :class="{
        'border-t-4': true,
        'border-primary': p.code === 'starter',
        'border-secondary': p.code === 'professional',
        'border-accent': p.code === 'enterprise',
      }" style="border-top-width: 4px;">
        <!-- Popular Badge -->
        <div v-if="p.code === 'professional'" class="absolute -top-3 right-4">
          <span class="badge badge-success shadow-lg">POPULAR</span>
        </div>
        
        <div class="text-center mb-4">
          <h3 class="text-2xl font-bold mb-2" :class="{
            'text-primary': p.code === 'starter',
            'text-secondary': p.code === 'professional',
            'text-accent': p.code === 'enterprise',
          }">{{ p.name }}</h3>
          <div class="text-4xl font-bold my-3" :class="{
            'text-primary': p.code === 'starter',
            'text-secondary': p.code === 'professional',
            'text-accent': p.code === 'enterprise',
          }">
            Rp {{ (p.price / 1000).toFixed(0) }}k
          </div>
          <p class="text-sm text-gray-500">per bulan</p>
        </div>
        
        <ul class="space-y-3 mb-6 text-gray-700">
          <li class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-success" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            {{ p.storage_gb }} GB Storage
          </li>
          <li class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-success" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Unlimited Users
          </li>
          <li class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-success" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Unlimited Records
          </li>
          <li class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-success" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span v-if="p.branches === -1">Unlimited Branches</span>
            <span v-else-if="p.branches === 1">{{ p.branches }} Branch</span>
            <span v-else>Up to {{ p.branches }} Branches</span>
          </li>
        </ul>
        
        <button v-if="sub?.plan !== p.code" class="btn-primary w-full">
          {{ sub ? 'Upgrade' : 'Select Plan' }}
        </button>
        <div v-else class="badge badge-success w-full text-center py-2">Current Plan</div>
      </div>
    </div>

    <div v-if="message" class="mt-6 alert alert-success">{{ message }}</div>
  </div>
</template>