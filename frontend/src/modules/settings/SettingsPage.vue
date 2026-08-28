<script setup lang="ts">
import { onMounted } from 'vue'
import { settingsApi, type ClinicSettings } from './api/settingsApi'
import { useApi } from '@/shared/composables/useApi'
import { ref } from 'vue'
import type { SubscriptionResource } from '@/shared/types/subscription'
import WhatsAppSettings from './WhatsAppSettings.vue'

const { data: settings, loading, refresh } = useApi<ClinicSettings>(() => settingsApi.get())
const saving = ref(false)
const message = ref('')

const form = ref<Record<string, string>>({
  company_name: '', legal_name: '', email: '', phone: '', website: '',
  address: '', city: '', province: '', postal_code: '',
  invoice_prefix: '', invoice_footer: '',
  billing_name: '', billing_email: '', billing_phone: '', billing_address: '',
})

onMounted(async () => {
  await refresh()
  if (settings.value) {
    const c = settings.value.clinic; const inv = settings.value.invoice; const b = settings.value.billing
    form.value = {
      company_name: c.name ?? '', legal_name: c.legal_name ?? '', email: c.email ?? '', phone: c.phone ?? '', website: c.website ?? '',
      address: c.address ?? '', city: c.city ?? '', province: c.province ?? '', postal_code: c.postal_code ?? '',
      invoice_prefix: inv.prefix ?? '', invoice_footer: inv.footer ?? '',
      billing_name: b.name ?? '', billing_email: b.email ?? '', billing_phone: b.phone ?? '', billing_address: b.address ?? '',
    }
  }
})

async function save(): Promise<void> {
  saving.value = true; message.value = ''
  try { await settingsApi.update(form.value); message.value = 'Settings saved.' } catch { message.value = 'Save failed.' }
  finally { saving.value = false }
}
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gradient-medical mb-2">Settings</h1>
      <p class="text-gray-600">Manage your clinic profile and preferences</p>
    </div>
    
    <div v-if="loading" class="text-center py-12">
      <div class="spinner mx-auto mb-4"></div>
      <p class="text-gray-500">Loading...</p>
    </div>
    <form v-else @submit.prevent="save" class="space-y-6">
      <!-- Clinic Profile -->
      <div class="card-medical">
        <h2 class="text-xl font-bold text-primary mb-6">Clinic Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-gray-700">Clinic Name</label><input v-model="form.company_name" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Legal Name</label><input v-model="form.legal_name" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Email</label><input v-model="form.email" type="email" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Phone</label><input v-model="form.phone" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Website</label><input v-model="form.website" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700">Address</label><textarea v-model="form.address" class="w-full px-3 py-2 border rounded mt-1" rows="2"></textarea></div>
          <div><label class="block text-sm font-medium text-gray-700">City</label><input v-model="form.city" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Province</label><input v-model="form.province" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Postal Code</label><input v-model="form.postal_code" class="w-full px-3 py-2 border rounded mt-1" /></div>
        </div>
      </div>
      <!-- Invoice Settings -->
      <div class="card-medical">
        <h2 class="text-xl font-bold text-primary mb-6">Invoice Settings</h2>
        <div class="grid grid-cols-1 gap-4">
          <div><label class="block text-sm font-medium text-gray-700">Invoice Prefix</label><input v-model="form.invoice_prefix" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Invoice Footer</label><textarea v-model="form.invoice_footer" class="w-full px-3 py-2 border rounded mt-1" rows="2"></textarea></div>
        </div>
      </div>
      <!-- Billing Info -->
      <div class="card-medical">
        <h2 class="text-xl font-bold text-primary mb-6">Billing Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-gray-700">Billing Name</label><input v-model="form.billing_name" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Billing Email</label><input v-model="form.billing_email" type="email" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div><label class="block text-sm font-medium text-gray-700">Billing Phone</label><input v-model="form.billing_phone" class="w-full px-3 py-2 border rounded mt-1" /></div>
          <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700">Billing Address</label><textarea v-model="form.billing_address" class="w-full px-3 py-2 border rounded mt-1" rows="2"></textarea></div>
        </div>
      </div>

      <div v-if="message" :class="message.includes('failed') ? 'alert alert-error' : 'alert alert-success'">{{ message }}</div>
      <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? 'Saving...' : 'Save Settings' }}</button>
    </form>

    <!-- WhatsApp Settings Section -->
    <WhatsAppSettings />
  </div>
</template>