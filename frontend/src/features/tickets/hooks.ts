import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  addComment,
  assignTicket,
  type CreateTicketInput,
  createTicket,
  getTicket,
  listComments,
  listTickets,
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
