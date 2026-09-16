import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import type { SubscriptionResource } from '@/shared/types/subscription'

export const subscriptionApi = {
  async get(): Promise<SubscriptionResource> {
    const { data } = await api.get<ApiResponse<SubscriptionResource>>('/v1/subscription')
    return data.data
  },
}