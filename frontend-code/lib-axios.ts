// src/lib/axios.ts
import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';

/**
 * Axios instance configured for Laravel Sanctum authentication
 * 
 * Features:
 * - CSRF protection via cookies
 * - Bearer token authentication
 * - Automatic token refresh on 401
 * - Request/response interceptors
 */

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://108.136.48.83:8080/api/v1';
const BASE_URL = API_URL.replace(/\/api\/v1$/, ''); // Strip /api/v1 for sanctum/csrf-cookie

// Create axios instance
export const axiosInstance = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Required for Sanctum CSRF cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000, // 30 seconds
});

/**
 * Initialize CSRF protection
 * Must be called before any authenticated requests
 */
export const initCsrf = async (): Promise<void> => {
  try {
    await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, {
      withCredentials: true,
    });
  } catch (error) {
    console.error('CSRF initialization failed:', error);
    throw error;
  }
};

/**
 * Storage keys for authentication
 */
const TOKEN_KEY = 'auth_token';
const REFRESH_ATTEMPT_KEY = 'refresh_attempt';

/**
 * Token management utilities
 */
export const tokenManager = {
  getToken: (): string | null => {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem(TOKEN_KEY);
  },
  
  setToken: (token: string): void => {
    if (typeof window === 'undefined') return;
    localStorage.setItem(TOKEN_KEY, token);
  },
  
  removeToken: (): void => {
    if (typeof window === 'undefined') return;
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(REFRESH_ATTEMPT_KEY);
  },
  
  isRefreshing: (): boolean => {
    if (typeof window === 'undefined') return false;
    return localStorage.getItem(REFRESH_ATTEMPT_KEY) === 'true';
  },
  
  setRefreshing: (refreshing: boolean): void => {
    if (typeof window === 'undefined') return;
    if (refreshing) {
      localStorage.setItem(REFRESH_ATTEMPT_KEY, 'true');
    } else {
      localStorage.removeItem(REFRESH_ATTEMPT_KEY);
    }
  },
};

/**
 * Request interceptor
 * Adds Bearer token to all requests if available
 */
axiosInstance.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = tokenManager.getToken();
    
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

/**
 * Response interceptor
 * Handles 401 Unauthorized with automatic token refresh
 */
axiosInstance.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };
    
    // Handle 401 Unauthorized
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      // Prevent infinite refresh loops
      if (tokenManager.isRefreshing()) {
        tokenManager.removeToken();
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
      
      try {
        tokenManager.setRefreshing(true);
        
        // Attempt token refresh
        const refreshResponse = await axiosInstance.post('/auth/refresh');
        const newToken = refreshResponse.data.data.access_token;
        
        if (newToken) {
          tokenManager.setToken(newToken);
          tokenManager.setRefreshing(false);
          
          // Retry original request with new token
          if (originalRequest.headers) {
            originalRequest.headers.Authorization = `Bearer ${newToken}`;
          }
          
          return axiosInstance(originalRequest);
        }
      } catch (refreshError) {
        // Refresh failed - logout user
        tokenManager.removeToken();
        
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
        
        return Promise.reject(refreshError);
      }
    }
    
    // Handle network errors
    if (error.message === 'Network Error') {
      console.error('Network error - check your connection or API server');
    }
    
    // Handle timeout
    if (error.code === 'ECONNABORTED') {
      console.error('Request timeout - server took too long to respond');
    }
    
    return Promise.reject(error);
  }
);

/**
 * API client wrapper with common methods
 */
export const apiClient = {
  /**
   * GET request
   */
  get: <T = any>(url: string, config?: any) => {
    return axiosInstance.get<T>(url, config);
  },
  
  /**
   * POST request
   */
  post: <T = any>(url: string, data?: any, config?: any) => {
    return axiosInstance.post<T>(url, data, config);
  },
  
  /**
   * PUT request
   */
  put: <T = any>(url: string, data?: any, config?: any) => {
    return axiosInstance.put<T>(url, data, config);
  },
  
  /**
   * PATCH request
   */
  patch: <T = any>(url: string, data?: any, config?: any) => {
    return axiosInstance.patch<T>(url, data, config);
  },
  
  /**
   * DELETE request
   */
  delete: <T = any>(url: string, config?: any) => {
    return axiosInstance.delete<T>(url, config);
  },
};

/**
 * Type-safe API response wrapper
 */
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
}

/**
 * API error handler
 */
export const handleApiError = (error: unknown): string => {
  if (axios.isAxiosError(error)) {
    const apiError = error.response?.data as ApiResponse<any> | undefined;
    
    if (apiError?.errors) {
      // Validation errors
      const firstError = Object.values(apiError.errors)[0];
      return firstError?.[0] || 'Validation error occurred';
    }
    
    if (apiError?.message) {
      return apiError.message;
    }
    
    if (error.message === 'Network Error') {
      return 'Network error - please check your connection';
    }
    
    if (error.code === 'ECONNABORTED') {
      return 'Request timeout - please try again';
    }
    
    return error.message || 'An error occurred';
  }
  
  return 'An unexpected error occurred';
};

export default axiosInstance;
