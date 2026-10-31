import { apiClient } from '../../lib/apiClient'

export interface Ticket {
  id: string
  number: number
  projectId: string
  sprintId: string | null
  title: string
  description: string | null
  columnId: string
  reporterId: string
  assigneeId: string | null
  carriedOverCount: number
}

export interface Comment {
  id: string
  ticketId: string
  authorId: string
  content: string
  createdAt: string
  updatedAt: string | null
}

export interface TicketSprintHistoryEntry {
  sprintId: string
  recordedAt: string
}

export function listTickets(projectId: string): Promise<Ticket[]> {
  return apiClient.get(`/api/projects/${projectId}/tickets`)
}

export type BacklogSort = 'oldest' | 'newest' | 'most_carried_over'

export interface BacklogItem {
  id: string
  number: number
  title: string
  assigneeId: string | null
  createdAt: string
  carriedOverCount: number
}

export interface BacklogPage {
  items: BacklogItem[]
  total: number
  page: number
  perPage: number
}

export interface BacklogQuery {
  search: string
  /** A member id, 'none' for unassigned tickets, or '' for everyone. */
  assignee: string
  sort: BacklogSort
  page: number
  perPage: number
}

export function listBacklog(projectId: string, query: BacklogQuery): Promise<BacklogPage> {
  const params = new URLSearchParams({ sort: query.sort, page: String(query.page), perPage: String(query.perPage) })
  if (query.search) params.set('search', query.search)
  if (query.assignee) params.set('assignee', query.assignee)
  return apiClient.get(`/api/projects/${projectId}/backlog?${params}`)
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

export function moveTicketToColumn(ticketId: string, columnId: string): Promise<Ticket> {
  return apiClient.patch(`/api/tickets/${ticketId}/column`, { columnId })
}

export function moveTicketToSprint(ticketId: string, sprintId: string | null): Promise<Ticket> {
  return apiClient.patch(`/api/tickets/${ticketId}/sprint`, { sprintId })
}

export function listComments(ticketId: string): Promise<Comment[]> {
  return apiClient.get(`/api/tickets/${ticketId}/comments`)
}

export function addComment(ticketId: string, content: string): Promise<Comment> {
  return apiClient.post(`/api/tickets/${ticketId}/comments`, { content })
}

export function editComment(commentId: string, content: string): Promise<Comment> {
  return apiClient.patch(`/api/comments/${commentId}`, { content })
}

export function getTicketSprintHistory(ticketId: string): Promise<TicketSprintHistoryEntry[]> {
  return apiClient.get(`/api/tickets/${ticketId}/sprint-history`)
}
