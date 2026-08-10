import api from '@/core/api/client'

export interface AuthUser {
  id: string
  name: string
  email: string
  organization_id: string
}

export const authApi = {
  async login(email: string, password: string): Promise<{ token: string; user: AuthUser }> {
    const { data } = await api.post('/v1/auth/login', { email, password })
    return data
  },

  async logout(): Promise<void> {
    await api.post('/v1/auth/logout')
  },

  async getCsrfCookie(): Promise<void> {
    await api.get('/sanctum/csrf-cookie')
  },
}