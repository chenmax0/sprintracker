import { apiClient } from '../../lib/apiClient'
import type { Sprint } from '../sprints/api'
import type { Ticket } from '../tickets/api'

export interface DemoSnapshot {
  team: { id: string; name: string } | null
  project: { id: string; teamId: string; name: string } | null
  sprints: Sprint[]
  tickets: Ticket[]
}

export function getDemoSnapshot(): Promise<DemoSnapshot> {
  return apiClient.get('/api/demo')
}
