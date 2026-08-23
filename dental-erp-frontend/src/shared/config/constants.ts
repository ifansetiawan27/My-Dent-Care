export const APP_NAME = process.env.NEXT_PUBLIC_APP_NAME || 'My Dent Care';
export const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080/api/v1';
export const APP_URL = process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000';

export const ROUTES = {
  HOME: '/',
  LOGIN: '/login',
  REGISTER: '/register',
  DASHBOARD: '/dashboard',
  PATIENTS: '/patients',
  APPOINTMENTS: '/appointments',
  TREATMENTS: '/treatments',
  BILLING: '/billing',
  INVENTORY: '/inventory',
  REPORTS: '/reports',
  SETTINGS: '/settings',
} as const;

export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    REGISTER: '/auth/register',
    REFRESH: '/auth/refresh',
    ME: '/auth/me',
  },
  PATIENTS: {
    LIST: '/patients',
    CREATE: '/patients',
    DETAIL: (id: string) => `/patients/${id}`,
    UPDATE: (id: string) => `/patients/${id}`,
    DELETE: (id: string) => `/patients/${id}`,
  },
  APPOINTMENTS: {
    LIST: '/appointments',
    CREATE: '/appointments',
    DETAIL: (id: string) => `/appointments/${id}`,
    UPDATE: (id: string) => `/appointments/${id}`,
    DELETE: (id: string) => `/appointments/${id}`,
  },
  TREATMENTS: {
    LIST: '/treatments',
    CREATE: '/treatments',
    DETAIL: (id: string) => `/treatments/${id}`,
    UPDATE: (id: string) => `/treatments/${id}`,
    DELETE: (id: string) => `/treatments/${id}`,
  },
  BILLING: {
    LIST: '/billing',
    CREATE: '/billing',
    DETAIL: (id: string) => `/billing/${id}`,
    UPDATE: (id: string) => `/billing/${id}`,
    DELETE: (id: string) => `/billing/${id}`,
  },
  INVENTORY: {
    LIST: '/inventory',
    CREATE: '/inventory',
    DETAIL: (id: string) => `/inventory/${id}`,
    UPDATE: (id: string) => `/inventory/${id}`,
    DELETE: (id: string) => `/inventory/${id}`,
  },
} as const;
