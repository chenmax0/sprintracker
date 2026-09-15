import { apiClient } from '../../lib/apiClient'

export type TicketStatus = 'todo' | 'in_progress' | 'done'

export interface Ticket {
  id: string
  projectId: string
  sprintId: string | null
  title: string
  description: string | null
  status: TicketStatus
  reporterId: string
  assigneeId: string | null
}

export interface Comment {
  id: string
  ticketId: string
  authorId: string
  content: string
  createdAt: string
}

export function listTickets(projectId: string): Promise<Ticket[]> {
  return apiClient.get(`/api/projects/${projectId}/tickets`)
}

export function getTicket(ticketId: string): Promise<Ticket> {
  return apiClient.get(`/api/tickets/${ticketId}`)
}

export interface CreateTicketInput {
  title: string
  description?: string
  sprintId?: string | null
  assigneeId?: string | null
}

export function createTicket(projectId: string, input: CreateTicketInput): Promise<Ticket> {
  return apiClient.post(`/api/projects/${projectId}/tickets`, input)
}

export function assignTicket(ticketId: string, assigneeId: string | null): Promise<Ticket> {
  return apiClient.post(`/api/tickets/${ticketId}/assign`, { assigneeId })
}

export function listComments(ticketId: string): Promise<Comment[]> {
  return apiClient.get(`/api/tickets/${ticketId}/comments`)
}

export function addComment(ticketId: string, content: string): Promise<Comment> {
  return apiClient.post(`/api/tickets/${ticketId}/comments`, { content })
}
