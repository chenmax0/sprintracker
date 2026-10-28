import { apiClient } from '../../lib/apiClient'

export type Granularity = 'hour' | 'day' | 'week' | 'month'

export interface VisitorBucket {
  /** Local start of the bucket (YYYY-MM-DDTHH:mm:ss, in the requested timezone). */
  start: string
  visitors: number
  users: number
}

export interface VisitorStats {
  granularity: Granularity
  timezone: string
  totals: { visitors: number; users: number }
  series: VisitorBucket[]
}

export interface VisitorStatsQuery {
  granularity: Granularity
  /** Local days, YYYY-MM-DD, both inclusive. */
  from: string
  to: string
  timezone: string
}

export function getVisitorStats({ granularity, from, to, timezone }: VisitorStatsQuery): Promise<VisitorStats> {
  const params = new URLSearchParams({ granularity, from, to, timezone })
  return apiClient.get(`/api/admin/analytics/visitors?${params}`)
}
