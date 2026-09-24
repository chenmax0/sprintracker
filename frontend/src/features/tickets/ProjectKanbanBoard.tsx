import { useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { ConfirmModal } from '../../lib/ConfirmModal'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Column } from '../board/api'
import { CreateColumnModal } from '../board/CreateColumnModal'
import { useColumns, useDeleteColumn, useRenameColumn, useReorderColumns } from '../board/hooks'
import type { Ticket } from './api'
import { useMoveTicketToColumn } from './hooks'
import { KanbanBoard } from './KanbanBoard'

/**
 * Wires the presentational KanbanBoard to a real project: ticket moves and
 * column edits are persisted (optimistically for ticket moves, with
 * rollback on error). Column creation/rename/reorder/delete only render
 * while editingColumns is true (toggled by BoardMenu, owned by the caller).
 */
interface ProjectKanbanBoardProps {
  projectId: string
  tickets: Ticket[]
  memberDirectory: MemberDirectory
  onTicketClick: (ticket: Ticket) => void
  editingColumns: boolean
}

export function ProjectKanbanBoard({ projectId, tickets, memberDirectory, onTicketClick, editingColumns }: ProjectKanbanBoardProps) {
  const { data: columns } = useColumns(projectId)
  const moveTicket = useMoveTicketToColumn(projectId)
  const renameColumn = useRenameColumn(projectId)
  const reorderColumns = useReorderColumns(projectId)
  const deleteColumn = useDeleteColumn(projectId)

  const [isCreatingColumn, setIsCreatingColumn] = useState(false)
  const [columnPendingDeletion, setColumnPendingDeletion] = useState<Column | null>(null)

  const mutationError = [moveTicket, renameColumn, reorderColumns].find((m) => m.isError)?.error

  if (!columns) {
    return null
  }

  function handleConfirmDelete() {
    if (!columnPendingDeletion) {
      return
    }
    deleteColumn.mutate(columnPendingDeletion.id, { onSuccess: () => setColumnPendingDeletion(null) })
  }

  return (
    <>
      <KanbanBoard
        columns={columns}
        tickets={tickets}
        memberDirectory={memberDirectory}
        onTicketClick={onTicketClick}
        onTicketMove={(ticketId, columnId) => moveTicket.mutate({ ticketId, columnId })}
        onReorderColumns={(columnIds) => reorderColumns.mutate(columnIds)}
        editingColumns={editingColumns}
        onRenameColumn={(columnId, name) => renameColumn.mutate({ columnId, name })}
        onRequestDeleteColumn={setColumnPendingDeletion}
        onAddColumn={() => setIsCreatingColumn(true)}
        errorMessage={mutationError instanceof ApiError ? mutationError.message : mutationError ? 'Une erreur est survenue.' : null}
      />
      {isCreatingColumn && <CreateColumnModal projectId={projectId} onClose={() => setIsCreatingColumn(false)} />}
      {columnPendingDeletion && (
        <ConfirmModal
          title="Supprimer la colonne"
          message={`Supprimer la colonne "${columnPendingDeletion.name}" ? Cette action est irréversible.`}
          isPending={deleteColumn.isPending}
          errorMessage={
            deleteColumn.isError
              ? deleteColumn.error instanceof ApiError
                ? deleteColumn.error.message
                : 'Suppression impossible'
              : null
          }
          onConfirm={handleConfirmDelete}
          onClose={() => setColumnPendingDeletion(null)}
        />
      )}
    </>
  )
}
