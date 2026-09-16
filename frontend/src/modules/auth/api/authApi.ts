import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import { uuid } from '@/shared/utils/uuid'

export interface AuthUser {
  id: string
  name: string
  email: string
  organization_id: string
  branch_id?: string
  /** Spatie role names, e.g. ["doctor"]. Drives portal selection. */
  roles?: string[]
  /** Spatie permission names, e.g. ["patient.view"]. Drives module visibility. */
  permissions?: string[]
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
  ): Promise<{ token: string; user: AuthUser; roles: string[]; permissions: string[] }> {
    // Simpan device_uuid agar konsisten antar sesi
    if (!localStorage.getItem('device_uuid')) {
      localStorage.setItem('device_uuid', uuid())
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

    // Response: data.data.access_token, data.data.user, data.data.roles,
    // data.data.permissions. Roles/permissions are emitted at the top level of
    // the login payload (not inside `user`) and drive portal selection.
    return {
      token: data.data.access_token,
      user: data.data.user,
      roles: data.data.roles ?? [],
      permissions: data.data.permissions ?? [],
    }
  },

  async logout(): Promise<void> {
    await api.post('/v1/auth/logout')
  },
}
