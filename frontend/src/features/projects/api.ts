import { apiClient } from '../../lib/apiClient'

export interface Project {
  id: string
  teamId: string
  name: string
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
