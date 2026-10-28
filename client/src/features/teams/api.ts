import { apiClient } from '../../lib/apiClient'

export interface TeamMember {
  memberId: string
  role: 'owner' | 'member'
  email: string
  name: string
}

export function listTeamMembers(teamId: string): Promise<TeamMember[]> {
  return apiClient.get(`/api/teams/${teamId}/members`)
}

export function addTeamMember(teamId: string, email: string): Promise<TeamMember> {
  return apiClient.post(`/api/teams/${teamId}/members`, { email })
}
