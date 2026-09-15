import { apiClient } from '../../lib/apiClient'

export interface Sprint {
  id: string
  projectId: string
  name: string
  startDate: string
  endDate: string
}

export function listSprints(projectId: string): Promise<Sprint[]> {
  return apiClient.get(`/api/projects/${projectId}/sprints`)
}

export function createSprint(projectId: string, startDate: string, endDate: string): Promise<Sprint> {
  return apiClient.post(`/api/projects/${projectId}/sprints`, { startDate, endDate })
}
