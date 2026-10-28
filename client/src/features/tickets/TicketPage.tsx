import { useParams } from 'react-router'
import { TicketDetails } from './TicketDetails'

export function TicketPage() {
  const { ticketId } = useParams<{ ticketId: string }>()
  if (!ticketId) throw new Error('Missing ticketId')

  return (
    <div className="mx-auto max-w-4xl">
      <TicketDetails ticketId={ticketId} showProjectLink />
    </div>
  )
}
