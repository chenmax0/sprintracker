import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { completeSprint, launchSprint, listSprints } from './api'

export function useSprints(projectId: string) {
  return useQuery({
    queryKey: ['projects', projectId, 'sprints'],
    queryFn: () => listSprints(projectId),
    enabled: projectId !== '',
  })
}

interface LaunchSprintInput {
  startDate: string
  endDate: string
}

export function useLaunchSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ startDate, endDate }: LaunchSprintInput) => launchSprint(projectId, startDate, endDate),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'sprints'] }),
  })
}

interface CompleteSprintInput {
  sprintId: string
  nextStartDate: string
  nextEndDate: string
}

/**
 * Completing a sprint also rolls unfinished tickets over to the sprint it
 * launches, so the project's ticket list is stale too, not just its sprints.
 */
export function useCompleteSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ sprintId, nextStartDate, nextEndDate }: CompleteSprintInput) =>
      completeSprint(sprintId, nextStartDate, nextEndDate),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'sprints'] })
      queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'tickets'] })
    },
  })
}
