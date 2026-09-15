import { ApiError } from '../../lib/apiClient'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Ticket } from './api'
import { useUpdateTicketStatus } from './hooks'
import { KanbanBoard } from './KanbanBoard'

/**
 * Wires the presentational KanbanBoard to a real project: status changes are
 * persisted via useUpdateTicketStatus (optimistic, with rollback on error).
 */
interface ProjectKanbanBoardProps {
  projectId: string
  tickets: Ticket[]
  memberDirectory: MemberDirectory
  onTicketClick: (ticket: Ticket) => void
}

export function ProjectKanbanBoard({ projectId, tickets, memberDirectory, onTicketClick }: ProjectKanbanBoardProps) {
  const updateStatus = useUpdateTicketStatus(projectId)

  return (
    <KanbanBoard
      tickets={tickets}
      memberDirectory={memberDirectory}
      onTicketClick={onTicketClick}
      onStatusChange={(ticketId, status) => updateStatus.mutate({ ticketId, status })}
      errorMessage={
        updateStatus.isError
          ? updateStatus.error instanceof ApiError
            ? updateStatus.error.message
            : 'Erreur lors du déplacement du ticket.'
          : null
      }
    />
  )
}
