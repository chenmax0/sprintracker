import { apiClient } from '../../lib/apiClient'

export interface Project {
  id: string
  teamId: string
  name: string
  isOwner: boolean
}

export interface MyProject extends Project {
  teamName: string
}

export function listMyProjects(): Promise<MyProject[]> {
  return apiClient.get('/api/projects')
}

export function getProject(projectId: string): Promise<Project> {
  return apiClient.get(`/api/projects/${projectId}`)
}

export function createMyProject(name: string): Promise<Project> {
  return apiClient.post('/api/projects', { name })
}

export function renameProject(projectId: string, name: string): Promise<Project> {
  return apiClient.patch(`/api/projects/${projectId}`, { name })
}

export function deleteProject(projectId: string): Promise<void> {
  return apiClient.delete(`/api/projects/${projectId}`)
}
