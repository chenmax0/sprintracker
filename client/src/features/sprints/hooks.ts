import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { invalidateTickets } from '../tickets/hooks'
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

/**
 * Launching a sprint also carries the previous sprint's unfinished tickets over
 * to it, so the project's ticket list is stale too, not just its sprints.
 */
export function useLaunchSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ startDate, endDate }: LaunchSprintInput) => launchSprint(projectId, startDate, endDate),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'sprints'] })
      invalidateTickets(queryClient, projectId)
    },
  })
}

export function useCompleteSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (sprintId: string) => completeSprint(sprintId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'sprints'] })
      // Unfinished tickets of the completed sprint now belong to the backlog.
      invalidateTickets(queryClient, projectId)
    },
  })
}
