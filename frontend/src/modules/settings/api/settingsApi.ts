import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'

export interface ClinicSettings {
  clinic: {
    name: string; legal_name: string | null
    email: string | null; phone: string | null; website: string | null
    address: string | null; city: string | null; province: string | null
    country: string; postal_code: string | null; logo: string | null
  }
  invoice: { prefix: string | null; footer: string | null }
  billing: { name: string | null; email: string | null; phone: string | null; address: string | null }
  subscription: import('@/shared/types/subscription').SubscriptionResource | null
}

export const settingsApi = {
  async get(): Promise<ClinicSettings> {
    const { data } = await api.get<ApiResponse<ClinicSettings>>('/v1/settings')
    return data.data
  },

  async update(payload: Record<string, unknown>): Promise<ClinicSettings> {
    const { data } = await api.put<ApiResponse<ClinicSettings>>('/v1/settings', payload)
    return data.data
  },
}