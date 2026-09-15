import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  type DragStartEvent,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  useDraggable,
  useDroppable,
  useSensor,
  useSensors,
} from '@dnd-kit/core'
import { type CSSProperties, useState } from 'react'
import { Link } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import type { Sprint } from '../sprints/api'
import type { Ticket, TicketStatus } from './api'
import { useUpdateTicketStatus } from './hooks'

const COLUMNS: { status: TicketStatus; label: string }[] = [
  { status: 'todo', label: 'À faire' },
  { status: 'in_progress', label: 'En cours' },
  { status: 'done', label: 'Terminé' },
]

function TicketCardContent({
  ticket,
  sprintLabel,
  dragHandleProps,
}: {
  ticket: Ticket
  sprintLabel: string
  dragHandleProps?: Record<string, unknown>
}) {
  return (
    <div className="flex items-start gap-2 rounded-md border border-gray-200 bg-white p-3 text-sm shadow-sm">
      <button
        type="button"
        {...dragHandleProps}
        className="mt-0.5 shrink-0 cursor-grab touch-none text-gray-300 hover:text-gray-500 active:cursor-grabbing"
        aria-label={`Déplacer le ticket "${ticket.title}"`}
      >
        ⠿
      </button>
      <div className="flex-1">
        <Link to={`/tickets/${ticket.id}`} className="font-medium text-gray-900 hover:underline">
          {ticket.title}
        </Link>
        <div className="mt-1 text-xs text-gray-400">{sprintLabel}</div>
      </div>
    </div>
  )
}

function DraggableTicketCard({ ticket, sprintLabel }: { ticket: Ticket; sprintLabel: string }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({ id: ticket.id })

  const style: CSSProperties = {
    transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
    opacity: isDragging ? 0.4 : 1,
  }

  return (
    <div ref={setNodeRef} style={style}>
      <TicketCardContent ticket={ticket} sprintLabel={sprintLabel} dragHandleProps={{ ...listeners, ...attributes }} />
    </div>
  )
}

function Column({
  status,
  label,
  tickets,
  sprintNames,
}: {
  status: TicketStatus
  label: string
  tickets: Ticket[]
  sprintNames: Map<string, string>
}) {
  const { setNodeRef, isOver } = useDroppable({ id: status })

  return (
    <div
      ref={setNodeRef}
      className={`flex w-72 shrink-0 flex-col gap-2 rounded-md border p-3 transition-colors ${
        isOver ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 bg-gray-100'
      }`}
    >
      <h3 className="text-xs font-semibold tracking-wide text-gray-500 uppercase">
        {label} <span className="text-gray-400">({tickets.length})</span>
      </h3>
      <div className="flex min-h-8 flex-col gap-2">
        {tickets.map((ticket) => (
          <DraggableTicketCard
            key={ticket.id}
            ticket={ticket}
            sprintLabel={(ticket.sprintId && sprintNames.get(ticket.sprintId)) || 'Backlog'}
          />
        ))}
        {tickets.length === 0 && <p className="px-1 text-xs text-gray-400">Aucun ticket</p>}
      </div>
    </div>
  )
}

export function KanbanBoard({ projectId, tickets, sprints }: { projectId: string; tickets: Ticket[]; sprints?: Sprint[] }) {
  const updateStatus = useUpdateTicketStatus(projectId)
  const [activeTicket, setActiveTicket] = useState<Ticket | null>(null)
  const sprintNames = new Map((sprints ?? []).map((sprint) => [sprint.id, sprint.name]))

  // Pointer needs a small movement threshold so a plain click still opens the
  // ticket link instead of always starting a drag. KeyboardSensor still
  // requires the drag handle to expose its listeners as tabIndex/role/keydown
  // props (done via dragHandleProps below) to actually be reachable.
  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 8 } }),
    useSensor(KeyboardSensor),
  )

  function handleDragStart(event: DragStartEvent) {
    setActiveTicket(tickets.find((ticket) => ticket.id === event.active.id) ?? null)
  }

  function handleDragEnd(event: DragEndEvent) {
    setActiveTicket(null)

    const { active, over } = event
    if (!over) {
      return
    }

    const newStatus = over.id as TicketStatus
    const ticket = tickets.find((t) => t.id === active.id)

    if (ticket && ticket.status !== newStatus) {
      updateStatus.mutate({ ticketId: ticket.id, status: newStatus })
    }
  }

  return (
    <div>
      {updateStatus.isError && (
        <p className="mb-2 text-sm text-red-600">
          {updateStatus.error instanceof ApiError ? updateStatus.error.message : "Erreur lors du déplacement du ticket."}
        </p>
      )}
      <DndContext
        sensors={sensors}
        collisionDetection={closestCenter}
        onDragStart={handleDragStart}
        onDragEnd={handleDragEnd}
      >
        <div className="flex gap-4 overflow-x-auto pb-2">
          {COLUMNS.map((column) => (
            <Column
              key={column.status}
              status={column.status}
              label={column.label}
              tickets={tickets.filter((ticket) => ticket.status === column.status)}
              sprintNames={sprintNames}
            />
          ))}
        </div>
        <DragOverlay>
          {activeTicket && (
            <TicketCardContent
              ticket={activeTicket}
              sprintLabel={(activeTicket.sprintId && sprintNames.get(activeTicket.sprintId)) || 'Backlog'}
            />
          )}
        </DragOverlay>
      </DndContext>
    </div>
  )
}
