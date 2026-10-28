import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import type { Ticket } from './api'
import { useMoveTicketToSprint } from './hooks'

export function BacklogModal({
  projectId,
  tickets,
  activeSprintId,
  onClose,
}: {
  projectId: string
  tickets: Ticket[]
  activeSprintId: string | null
  onClose: () => void
}) {
  const moveToSprint = useMoveTicketToSprint(projectId)

  return (
    <Modal title="Backlog" onClose={onClose}>
      {!activeSprintId && (
        <p className="mb-3 text-sm text-gray-500">
          Lancez un sprint pour pouvoir y ajouter des tickets du backlog.
        </p>
      )}
      <ul className="flex flex-col gap-2">
        {tickets.map((ticket) => (
          <li
            key={ticket.id}
            className="flex items-center justify-between gap-2 rounded-md border border-gray-200 p-3 text-sm"
          >
            <span className="text-gray-900">
              <span className="mr-1 text-gray-400">#{ticket.number}</span>
              {ticket.title}
            </span>
            <button
              type="button"
              disabled={!activeSprintId || moveToSprint.isPending}
              onClick={() => moveToSprint.mutate({ ticketId: ticket.id, sprintId: activeSprintId })}
              className="shrink-0 rounded-md border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Ajouter au sprint
            </button>
          </li>
        ))}
        {tickets.length === 0 && <p className="text-sm text-gray-500">Le backlog est vide.</p>}
      </ul>
      {moveToSprint.isError && (
        <p className="mt-2 text-sm text-red-600">
          {moveToSprint.error instanceof ApiError ? moveToSprint.error.message : "Erreur lors de l'ajout au sprint"}
        </p>
      )}
    </Modal>
  )
}
