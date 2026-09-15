import { apiClient } from '../../lib/apiClient'

export interface Team {
  id: string
  name: string
}

export interface TeamMember {
  memberId: string
  role: 'owner' | 'member'
}

export function listTeams(): Promise<Team[]> {
  return apiClient.get('/api/teams')
}

export function getTeam(teamId: string): Promise<Team> {
  return apiClient.get(`/api/teams/${teamId}`)
}

export function createTeam(name: string): Promise<Team> {
  return apiClient.post('/api/teams', { name })
}

export function listTeamMembers(teamId: string): Promise<TeamMember[]> {
  return apiClient.get(`/api/teams/${teamId}/members`)
}

export function addTeamMember(teamId: string, email: string): Promise<TeamMember> {
  return apiClient.post(`/api/teams/${teamId}/members`, { email })
}
