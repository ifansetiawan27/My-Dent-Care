export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface User {
  id: string;
  name: string;
  email: string;
  role: string;
  clinic_id?: string;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  expires_at: string;
}
