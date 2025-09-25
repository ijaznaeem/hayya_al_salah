export interface User {
  id: number;
  username: string;
  email: string;
  role?: string;
  last_login?: string;
  created_at: string;
  updated_at?: string;
  isAdmin?: boolean;
  isActive?: boolean;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  user: User;
  token: string;
  message: string;
}
