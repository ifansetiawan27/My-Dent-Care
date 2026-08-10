import { ref, type Ref } from 'vue'
import api from '@/core/api/client'

export function useApi<T>(fetcher: () => Promise<T>): { data: Ref<T | null>; loading: Ref<boolean>; error: Ref<string | null>; refresh: () => Promise<void> } {
  const data = ref<T | null>(null) as Ref<T | null>
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function refresh(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      data.value = await fetcher()
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message ?? 'Request failed.'
    } finally {
      loading.value = false
    }
  }

  return { data, loading, error, refresh }
}