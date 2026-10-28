import { apiClient } from '../../lib/apiClient'
import type { Column } from '../board/api'
import type { Sprint } from '../sprints/api'
import type { TeamMember } from '../teams/api'
import type { Ticket } from '../tickets/api'

export interface DemoSnapshot {
  team: { id: string; name: string } | null
  project: { id: string; teamId: string; name: string } | null
  members: TeamMember[]
  sprints: Sprint[]
  tickets: Ticket[]
  columns: Column[]
}

export function getDemoSnapshot(): Promise<DemoSnapshot> {
  return apiClient.get('/api/demo')
}
