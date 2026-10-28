import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { getVisitorStats, type VisitorStatsQuery } from './api'

export function useVisitorStats(query: VisitorStatsQuery) {
  return useQuery({
    queryKey: ['admin', 'visitors', query],
    queryFn: () => getVisitorStats(query),
    // Keep the previous chart on screen while a new range loads: no skeleton flash.
    placeholderData: keepPreviousData,
  })
}
