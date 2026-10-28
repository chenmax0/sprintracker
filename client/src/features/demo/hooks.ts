import { useQuery } from '@tanstack/react-query'
import { getDemoSnapshot } from './api'

export function useDemoSnapshot() {
  return useQuery({ queryKey: ['demo'], queryFn: getDemoSnapshot })
}
