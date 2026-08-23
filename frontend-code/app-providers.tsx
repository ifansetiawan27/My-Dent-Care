// src/app/providers.tsx
'use client';

import { ReactNode, useState } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AnimatePresence } from 'framer-motion';

/**
 * Providers Component
 * 
 * Wraps the application with necessary providers:
 * - TanStack React Query for data fetching
 * - Framer Motion AnimatePresence for page transitions
 */
export function Providers({ children }: { children: ReactNode }) {
  // Create Query Client instance
  const [queryClient] = useState(
    () =>
      new QueryClient({
        defaultOptions: {
          queries: {
            // Stale time: 5 minutes
            staleTime: 5 * 60 * 1000,
            // Cache time: 10 minutes
            gcTime: 10 * 60 * 1000,
            // Retry failed requests 3 times
            retry: 3,
            // Retry delay with exponential backoff
            retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
            // Refetch on window focus for fresh data
            refetchOnWindowFocus: true,
            // Don't refetch on mount if data is fresh
            refetchOnMount: false,
          },
          mutations: {
            // Retry mutations once
            retry: 1,
          },
        },
      })
  );

  return (
    <QueryClientProvider client={queryClient}>
      <AnimatePresence mode="wait" initial={false}>
        {children}
      </AnimatePresence>
    </QueryClientProvider>
  );
}
