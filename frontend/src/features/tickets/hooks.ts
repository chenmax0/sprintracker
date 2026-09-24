import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  addComment,
  assignTicket,
  type CreateTicketInput,
  createTicket,
  getTicket,
  getTicketSprintHistory,
  listComments,
  listTickets,
  moveTicketToColumn,
  moveTicketToSprint,
  type Ticket,
} from './api'

export function useTickets(projectId: string) {
  return useQuery({ queryKey: ['projects', projectId, 'tickets'], queryFn: () => listTickets(projectId) })
}

export function useTicket(ticketId: string) {
  return useQuery({ queryKey: ['tickets', ticketId], queryFn: () => getTicket(ticketId) })
}

export function useCreateTicket(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (input: CreateTicketInput) => createTicket(projectId, input),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'tickets'] }),
  })
}

export function useAssignTicket(ticketId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (assigneeId: string | null) => assignTicket(ticketId, assigneeId),
    onSuccess: (ticket) => {
      queryClient.setQueryData(['tickets', ticketId], ticket)
      queryClient.invalidateQueries({ queryKey: ['projects', ticket.projectId, 'tickets'] })
    },
  })
}

interface MoveTicketToColumnInput {
  ticketId: string
  columnId: string
}

/**
 * Optimistic column move, for the kanban board's drag-and-drop: the card
 * must move immediately on drop, not wait for the round-trip. Rolls back to
 * the pre-drag snapshot if the request fails.
 */
export function useMoveTicketToColumn(projectId: string) {
  const queryClient = useQueryClient()
  const queryKey = ['projects', projectId, 'tickets']

  return useMutation({
    mutationFn: ({ ticketId, columnId }: MoveTicketToColumnInput) => moveTicketToColumn(ticketId, columnId),
    onMutate: async ({ ticketId, columnId }) => {
      await queryClient.cancelQueries({ queryKey })
      const previousTickets = queryClient.getQueryData<Ticket[]>(queryKey)
      const previousColumnId = previousTickets?.find((ticket) => ticket.id === ticketId)?.columnId

      queryClient.setQueryData<Ticket[]>(queryKey, (tickets) =>
        tickets?.map((ticket) => (ticket.id === ticketId ? { ...ticket, columnId } : ticket)),
      )

      return { ticketId, previousColumnId }
    },
    onError: (_error, _variables, context) => {
      if (!context || context.previousColumnId === undefined) {
        return
      }

      queryClient.setQueryData<Ticket[]>(queryKey, (tickets) =>
        tickets?.map((ticket) =>
          ticket.id === context.ticketId ? { ...ticket, columnId: context.previousColumnId! } : ticket,
        ),
      )
    },
    onSettled: () => queryClient.invalidateQueries({ queryKey }),
  })
}

export function useMoveTicketToSprint(projectId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ ticketId, sprintId }: { ticketId: string; sprintId: string | null }) =>
      moveTicketToSprint(ticketId, sprintId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['projects', projectId, 'tickets'] }),
  })
}

export function useComments(ticketId: string) {
  return useQuery({ queryKey: ['tickets', ticketId, 'comments'], queryFn: () => listComments(ticketId) })
}

export function useAddComment(ticketId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (content: string) => addComment(ticketId, content),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['tickets', ticketId, 'comments'] }),
  })
}

export function useTicketSprintHistory(ticketId: string) {
  return useQuery({ queryKey: ['tickets', ticketId, 'sprint-history'], queryFn: () => getTicketSprintHistory(ticketId) })
}
