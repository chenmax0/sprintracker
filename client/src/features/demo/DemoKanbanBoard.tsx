import { useState } from 'react'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Column } from '../board/api'
import type { Sprint } from '../sprints/api'
import type { Ticket } from '../tickets/api'
import { KanbanBoard } from '../tickets/KanbanBoard'
import { DemoTicketModal } from './DemoTicketModal'

/**
 * Demo mode has no account, so drag-and-drop can't call the write API (it's
 * unauthenticated and the demo project is shared/read-only). Column moves
 * are kept in local component state only, reset whenever a fresh snapshot
 * loads, so visitors can try the board without affecting the shared demo
 * data. Column editing (create/rename/reorder/delete) isn't offered here -
 * KanbanBoard only shows it when those handler props are passed.
 */
interface DemoKanbanBoardProps {
  tickets: Ticket[]
  columns?: Column[]
  sprints?: Sprint[]
  memberDirectory?: MemberDirectory
}

export function DemoKanbanBoard({ tickets, columns, sprints, memberDirectory }: DemoKanbanBoardProps) {
  const [syncedTickets, setSyncedTickets] = useState(tickets)
  const [localTickets, setLocalTickets] = useState(tickets)
  const [selectedTicket, setSelectedTicket] = useState<Ticket | null>(null)

  if (tickets !== syncedTickets) {
    setSyncedTickets(tickets)
    setLocalTickets(tickets)
  }

  function handleTicketMove(ticketId: string, columnId: string) {
    setLocalTickets((current) => current.map((ticket) => (ticket.id === ticketId ? { ...ticket, columnId } : ticket)))
  }

  // The demo simulates a single active sprint: backlog and past-sprint
  // tickets have nothing to do on a sprint board and are hidden.
  const activeSprint = sprints?.find((sprint) => sprint.status === 'active') ?? null
  const sprintTickets = activeSprint ? localTickets.filter((ticket) => ticket.sprintId === activeSprint.id) : []

  const sprintNames = new Map((sprints ?? []).map((sprint) => [sprint.id, sprint.name]))
  const selectedSprintLabel = (selectedTicket?.sprintId && sprintNames.get(selectedTicket.sprintId)) || ''
  const columnNames = new Map((columns ?? []).map((column) => [column.id, column.name]))
  const selectedColumnName = (selectedTicket && columnNames.get(selectedTicket.columnId)) || ''

  return (
    <>
      <KanbanBoard
        columns={columns ?? []}
        tickets={sprintTickets}
        memberDirectory={memberDirectory}
        onTicketMove={handleTicketMove}
        onTicketClick={setSelectedTicket}
      />
      {selectedTicket && (
        <DemoTicketModal
          ticket={selectedTicket}
          columnName={selectedColumnName}
          sprintLabel={selectedSprintLabel}
          memberDirectory={memberDirectory}
          onClose={() => setSelectedTicket(null)}
        />
      )}
    </>
  )
}
