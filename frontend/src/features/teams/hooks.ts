import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { addTeamMember, listTeamMembers } from './api'

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
