import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { addTeamMember, createTeam, getTeam, listTeamMembers, listTeams } from './api'

export function useTeams() {
  return useQuery({ queryKey: ['teams'], queryFn: listTeams })
}

export function useTeam(teamId: string) {
  return useQuery({ queryKey: ['teams', teamId], queryFn: () => getTeam(teamId) })
}

export function useCreateTeam() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (name: string) => createTeam(name),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['teams'] }),
  })
}

export function useTeamMembers(teamId: string) {
  return useQuery({
    queryKey: ['teams', teamId, 'members'],
    queryFn: () => listTeamMembers(teamId),
    enabled: teamId !== '',
  })
}

export function useAddTeamMember(teamId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (email: string) => addTeamMember(teamId, email),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['teams', teamId, 'members'] }),
  })
}
