import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'

export interface AuthUser {
  id: string
  name: string
  email: string
  organization_id: string
  branch_id?: string
}

export interface LookupResult {
  organization_id: string
  branch_id: string
  organization: string
  branch: string
}

export const authApi = {
  async profile(): Promise<AuthUser> {
    const { data } = await api.get<ApiResponse<AuthUser>>('/v1/auth/profile')
    return data.data
  },

  async lookup(identifier: string): Promise<LookupResult> {
    const { data } = await api.post('/v1/auth/lookup', { identifier })
    return data.data
  },

  async login(
    identifier: string,
    password: string,
    organizationId: string,
    branchId: string,
  ): Promise<{ token: string; user: AuthUser }> {
    // Simpan device_uuid agar konsisten antar sesi
    if (!localStorage.getItem('device_uuid')) {
      localStorage.setItem('device_uuid', crypto.randomUUID())
    }
    const deviceUuid = localStorage.getItem('device_uuid')!

    const { data } = await api.post('/v1/auth/login', {
      identifier,
      password,
      organization_id: organizationId,
      branch_id: branchId,
      device_uuid: deviceUuid,
      device_name: navigator.userAgent.substring(0, 100),
      device_type: 'web',
      platform: 'web',
    })

    // Response: data.data.access_token, data.data.user
    return {
      token: data.data.access_token,
      user: data.data.user,
    }
  },

  async logout(): Promise<void> {
    await api.post('/v1/auth/logout')
  },

  async getCsrfCookie(): Promise<void> {
    await api.get('/sanctum/csrf-cookie')
  },
}
