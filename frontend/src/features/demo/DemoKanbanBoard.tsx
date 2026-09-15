import { useState } from 'react'
import type { Sprint } from '../sprints/api'
import type { Ticket, TicketStatus } from '../tickets/api'
import { KanbanBoard } from '../tickets/KanbanBoard'

/**
 * Demo mode has no account, so drag-and-drop can't call the write API (it's
 * unauthenticated and the demo project is shared/read-only). Status changes
 * are kept in local component state only, reset whenever a fresh snapshot
 * loads, so visitors can try the board without affecting the shared demo data.
 */
export function DemoKanbanBoard({ tickets, sprints }: { tickets: Ticket[]; sprints?: Sprint[] }) {
  const [syncedTickets, setSyncedTickets] = useState(tickets)
  const [localTickets, setLocalTickets] = useState(tickets)

  if (tickets !== syncedTickets) {
    setSyncedTickets(tickets)
    setLocalTickets(tickets)
  }

  function handleStatusChange(ticketId: string, status: TicketStatus) {
    setLocalTickets((current) => current.map((ticket) => (ticket.id === ticketId ? { ...ticket, status } : ticket)))
  }

  return <KanbanBoard tickets={localTickets} sprints={sprints} onStatusChange={handleStatusChange} />
}
