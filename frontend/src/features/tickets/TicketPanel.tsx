import { useEscapeKey } from '../../lib/useEscapeKey'
import { TicketDetails } from './TicketDetails'

/**
 * Slide-over panel opened from the kanban board: shows the full ticket
 * detail without navigating away, so the sprint board stays mounted
 * underneath and the user's place on it isn't lost.
 */
export function TicketPanel({ ticketId, onClose }: { ticketId: string; onClose: () => void }) {
  useEscapeKey(onClose)

  return (
    <div role="presentation" className="fixed inset-0 z-10 flex justify-end bg-black/30" onClick={onClose}>
      <div
        role="dialog"
        aria-modal="true"
        className="h-full w-full max-w-xl overflow-y-auto bg-gray-50 p-6 shadow-xl"
        onClick={(e) => e.stopPropagation()}
      >
        <button
          type="button"
          onClick={onClose}
          className="mb-4 flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
        >
          ✕ Fermer
        </button>
        <TicketDetails ticketId={ticketId} />
      </div>
    </div>
  )
}
