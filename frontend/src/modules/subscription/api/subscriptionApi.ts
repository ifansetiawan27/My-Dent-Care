import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import type { SubscriptionResource, PlanResource } from '@/shared/types/subscription'

export const subscriptionApi = {
  async get(): Promise<SubscriptionResource> {
    const { data } = await api.get<ApiResponse<SubscriptionResource>>('/v1/subscription')
    return data.data
  },

  async getPlans(): Promise<PlanResource[]> {
    const { data } = await api.get<ApiResponse<PlanResource[]>>('/v1/subscription/plans')
    return data.data
  },

  async cancel(): Promise<SubscriptionResource> {
    const { data } = await api.post<ApiResponse<SubscriptionResource>>('/v1/subscription/cancel')
    return data.data
  },
}