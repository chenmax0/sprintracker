import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiError } from '../../lib/apiClient'
import { login, logout, me, register } from './api'

export const meQueryKey = ['auth', 'me']

export function useMe() {
  return useQuery({
    queryKey: meQueryKey,
    queryFn: me,
    retry: false,
    staleTime: 5 * 60 * 1000,
  })
}

export function useLogin() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) => login(email, password),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: meQueryKey }),
  })
}

export function useRegister() {
  return useMutation({
    mutationFn: ({ email, name, password }: { email: string; name: string; password: string }) =>
      register(email, name, password),
  })
}

export function useLogout() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: logout,
    onSuccess: () => queryClient.setQueryData(meQueryKey, undefined),
  })
}

export function isUnauthorized(error: unknown): boolean {
  return error instanceof ApiError && error.status === 401
}
