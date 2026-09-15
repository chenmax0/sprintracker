import { apiClient } from '../../lib/apiClient'

export type SprintStatus = 'active' | 'completed'

export interface Sprint {
  id: string
  projectId: string
  name: string
  startDate: string
  endDate: string
  status: SprintStatus
}

export function listSprints(projectId: string): Promise<Sprint[]> {
  return apiClient.get(`/api/projects/${projectId}/sprints`)
}

export function launchSprint(projectId: string, startDate: string, endDate: string): Promise<Sprint> {
  return apiClient.post(`/api/projects/${projectId}/sprints`, { startDate, endDate })
}

export function completeSprint(sprintId: string, nextStartDate: string, nextEndDate: string): Promise<Sprint> {
  return apiClient.post(`/api/sprints/${sprintId}/complete`, { nextStartDate, nextEndDate })
}
