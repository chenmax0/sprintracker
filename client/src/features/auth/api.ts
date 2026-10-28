import { apiClient } from '../../lib/apiClient'

export interface User {
  id: string
  email: string
  name: string
}

export function login(email: string, password: string): Promise<{ token: string }> {
  return apiClient.post('/api/login', { email, password })
}

export function register(email: string, name: string, password: string): Promise<User> {
  return apiClient.post('/api/register', { email, name, password })
}

export function me(): Promise<User> {
  return apiClient.get('/api/me')
}

export function logout(): Promise<void> {
  return apiClient.post('/api/logout')
}
