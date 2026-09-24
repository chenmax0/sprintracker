import { ApiError } from '../../lib/apiClient'
import type { MemberDirectory } from '../../lib/memberDirectory'
import { useColumns, useReorderColumns } from '../board/hooks'
import type { Ticket } from './api'
import { useMoveTicketToColumn } from './hooks'
import { KanbanBoard } from './KanbanBoard'

/**
 * Wires the presentational KanbanBoard to a real project: ticket moves and
 * column reordering are persisted (optimistically for ticket moves, with
 * rollback on error). Creating/renaming/deleting columns lives in
 * ColumnManagerMenu, rendered alongside this board by the caller.
 */
interface ProjectKanbanBoardProps {
  projectId: string
  tickets: Ticket[]
  memberDirectory: MemberDirectory
  onTicketClick: (ticket: Ticket) => void
}

export function ProjectKanbanBoard({ projectId, tickets, memberDirectory, onTicketClick }: ProjectKanbanBoardProps) {
  const { data: columns } = useColumns(projectId)
  const moveTicket = useMoveTicketToColumn(projectId)
  const reorderColumns = useReorderColumns(projectId)

  const mutationError = moveTicket.isError ? moveTicket.error : reorderColumns.isError ? reorderColumns.error : null

  if (!columns) {
    return null
  }

  return (
    <KanbanBoard
      columns={columns}
      tickets={tickets}
      memberDirectory={memberDirectory}
      onTicketClick={onTicketClick}
      onTicketMove={(ticketId, columnId) => moveTicket.mutate({ ticketId, columnId })}
      onReorderColumns={(columnIds) => reorderColumns.mutate(columnIds)}
      errorMessage={mutationError instanceof ApiError ? mutationError.message : mutationError ? 'Une erreur est survenue.' : null}
    />
  )
}
