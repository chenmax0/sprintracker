import { MemberLabel } from '../../lib/MemberLabel'
import type { MemberDirectory } from '../../lib/memberDirectory'
import { Modal } from '../../lib/Modal'
import type { Ticket } from '../tickets/api'

const STATUS_LABELS: Record<Ticket['status'], string> = {
  todo: 'À faire',
  in_progress: 'En cours',
  done: 'Terminé',
}

interface DemoTicketModalProps {
  ticket: Ticket
  sprintLabel: string
  memberDirectory?: MemberDirectory
  onClose: () => void
}

export function DemoTicketModal({ ticket, sprintLabel, memberDirectory, onClose }: DemoTicketModalProps) {
  return (
    <Modal title={`#${ticket.number} ${ticket.title}`} onClose={onClose}>
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
    </Modal>
  )
}
