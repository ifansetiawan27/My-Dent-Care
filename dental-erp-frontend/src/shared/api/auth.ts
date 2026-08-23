import apiClient from './client';
import { API_ENDPOINTS } from '../config/constants';
import { ApiResponse, AuthResponse, User } from '../types/api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export const authApi = {
  login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const { data } = await apiClient.post<ApiResponse<AuthResponse>>(
      API_ENDPOINTS.AUTH.LOGIN,
      credentials
    );
    return data.data;
  },

  register: async (userData: RegisterData): Promise<AuthResponse> => {
    const { data } = await apiClient.post<ApiResponse<AuthResponse>>(
      API_ENDPOINTS.AUTH.REGISTER,
      userData
    );
    return data.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post(API_ENDPOINTS.AUTH.LOGOUT);
  },

  me: async (): Promise<User> => {
    const { data } = await apiClient.get<ApiResponse<User>>(
      API_ENDPOINTS.AUTH.ME
    );
    return data.data;
  },

  refreshToken: async (): Promise<AuthResponse> => {
    const { data } = await apiClient.post<ApiResponse<AuthResponse>>(
      API_ENDPOINTS.AUTH.REFRESH
    );
    return data.data;
  },
};
