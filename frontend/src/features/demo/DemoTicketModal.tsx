import { useEffect } from 'react'
import { MemberLabel } from '../../lib/MemberLabel'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Ticket } from '../tickets/api'

const STATUS_LABELS: Record<Ticket['status'], string> = {
  todo: 'À faire',
  in_progress: 'En cours',
  done: 'Terminé',
}

export function DemoTicketModal({
  ticket,
  sprintLabel,
  memberDirectory,
  onClose,
}: {
  ticket: Ticket
  sprintLabel: string
  memberDirectory?: MemberDirectory
  onClose: () => void
}) {
  useEffect(() => {
    function handleKeyDown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        onClose()
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [onClose])

  return (
    <div
      role="presentation"
      className="fixed inset-0 z-10 flex items-center justify-center bg-black/30 px-4"
      onClick={onClose}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="demo-ticket-title"
        className="w-full max-w-lg rounded-md bg-white p-6 shadow-lg"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="mb-4 flex items-start justify-between gap-4">
          <h2 id="demo-ticket-title" className="text-lg font-semibold text-gray-900">
            {ticket.title}
          </h2>
          <button
            type="button"
            onClick={onClose}
            aria-label="Fermer"
            className="text-gray-400 hover:text-gray-600"
          >
            ✕
          </button>
        </div>

        {ticket.description && <p className="mb-4 text-sm text-gray-600">{ticket.description}</p>}

        <div className="flex flex-wrap gap-6 text-sm">
          <div>
            <span className="block text-xs text-gray-500">Statut</span>
            <span className="font-medium text-gray-900">{STATUS_LABELS[ticket.status]}</span>
          </div>
          <div>
            <span className="block text-xs text-gray-500">Sprint</span>
            <span className="font-medium text-gray-900">{sprintLabel}</span>
          </div>
          <div>
            <span className="block text-xs text-gray-500">Rapporté par</span>
            <MemberLabel memberId={ticket.reporterId} directory={memberDirectory} />
          </div>
          <div>
            <span className="block text-xs text-gray-500">Assigné à</span>
            {ticket.assigneeId ? (
              <MemberLabel memberId={ticket.assigneeId} directory={memberDirectory} />
            ) : (
              <span className="text-gray-400">Personne</span>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
