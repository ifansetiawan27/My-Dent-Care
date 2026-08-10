<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/core/auth/useAuth'
import { useRouter } from 'vue-router'

const { login, loading, error: authError } = useAuth()
const router = useRouter()
const email = ref('')
const password = ref('')
const localError = ref<string | null>(null)

async function handleLogin(): Promise<void> {
  localError.value = null
  try {
    await login(email.value, password.value)
    router.push('/')
  } catch {
    localError.value = authError.value ?? 'Login failed.'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
      <h1 class="text-2xl font-bold mb-2 text-center">DentalERP</h1>
      <p class="text-gray-500 text-center mb-6">Sign in to your clinic account</p>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="email" type="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="admin@clinic.id" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="password" type="password" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="••••••••" />
        </div>

        <div v-if="localError" class="text-red-600 text-sm bg-red-50 p-3 rounded">{{ localError }}</div>

        <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium">
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>
    </div>
  </div>
</template>