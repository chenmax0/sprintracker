import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createSprint, listSprints } from './api'

export function useSprints(projectId: string) {
  return useQuery({ queryKey: ['projects', projectId, 'sprints'], queryFn: () => listSprints(projectId) })
}

interface CreateSprintInput {
  startDate: string
  endDate: string
}

export function useCreateSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ startDate, endDate }: CreateSprintInput) => createSprint(projectId, startDate, endDate),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'sprints'] }),
  })
}
