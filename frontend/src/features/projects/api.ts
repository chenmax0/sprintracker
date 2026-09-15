import { apiClient } from '../../lib/apiClient'

export interface Project {
  id: string
  teamId: string
  name: string
}

export function listProjects(teamId: string): Promise<Project[]> {
  return apiClient.get(`/api/teams/${teamId}/projects`)
}

export function getProject(projectId: string): Promise<Project> {
  return apiClient.get(`/api/projects/${projectId}`)
}

export function createProject(teamId: string, name: string): Promise<Project> {
  return apiClient.post(`/api/teams/${teamId}/projects`, { name })
}
