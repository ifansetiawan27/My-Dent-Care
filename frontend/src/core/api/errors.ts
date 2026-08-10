import type { AxiosError } from 'axios'

export interface ApiError {
  status: number
  message: string
  code?: string
  action?: string
  errors?: Record<string, string[]>
}

export function normalizeError(error: unknown): ApiError {
  const axiosError = error as AxiosError<{ message?: string; errors?: Record<string, string[]>; code?: string; action?: string }>
  const response = axiosError.response
  const data = response?.data

  return {
    status: response?.status ?? 0,
    message: data?.message ?? axiosError.message ?? 'An unexpected error occurred.',
    code: data?.code,
    action: data?.action,
    errors: data?.errors,
  }
}

export function isSubscriptionExpired(error: unknown): boolean {
  const axiosError = error as AxiosError<{ code?: string }>
  return axiosError.response?.data?.code === 'SUBSCRIPTION_EXPIRED'
}