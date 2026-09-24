import { ApiError } from '../../lib/apiClient'
import type { MemberDirectory } from '../../lib/memberDirectory'
import { useColumns, useCreateColumn, useDeleteColumn, useRenameColumn, useReorderColumns } from '../board/hooks'
import type { Ticket } from './api'
import { useMoveTicketToColumn } from './hooks'
import { KanbanBoard } from './KanbanBoard'

/**
 * Wires the presentational KanbanBoard to a real project: ticket moves and
 * column edits are persisted (optimistically for ticket moves, with
 * rollback on error).
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
  const createColumn = useCreateColumn(projectId)
  const renameColumn = useRenameColumn(projectId)
  const reorderColumns = useReorderColumns(projectId)
  const deleteColumn = useDeleteColumn(projectId)

  const mutationError = [moveTicket, createColumn, renameColumn, reorderColumns, deleteColumn].find((m) => m.isError)
    ?.error

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
      onCreateColumn={(name) => createColumn.mutate(name)}
      onRenameColumn={(columnId, name) => renameColumn.mutate({ columnId, name })}
      onReorderColumns={(columnIds) => reorderColumns.mutate(columnIds)}
      onDeleteColumn={(columnId) => deleteColumn.mutate(columnId)}
      errorMessage={mutationError instanceof ApiError ? mutationError.message : mutationError ? 'Une erreur est survenue.' : null}
    />
  )
}
