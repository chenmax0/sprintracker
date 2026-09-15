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
import type { Ticket, TicketStatus } from './api'
import { useUpdateTicketStatus } from './hooks'

const COLUMNS: { status: TicketStatus; label: string }[] = [
  { status: 'todo', label: 'À faire' },
  { status: 'in_progress', label: 'En cours' },
  { status: 'done', label: 'Terminé' },
]

function TicketCardContent({ ticket, dragHandleProps }: { ticket: Ticket; dragHandleProps?: Record<string, unknown> }) {
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
      <Link to={`/tickets/${ticket.id}`} className="flex-1 font-medium text-gray-900 hover:underline">
        {ticket.title}
      </Link>
    </div>
  )
}

function DraggableTicketCard({ ticket }: { ticket: Ticket }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({ id: ticket.id })

  const style: CSSProperties = {
    transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
    opacity: isDragging ? 0.4 : 1,
  }

  return (
    <div ref={setNodeRef} style={style}>
      <TicketCardContent ticket={ticket} dragHandleProps={{ ...listeners, ...attributes }} />
    </div>
  )
}

function Column({ status, label, tickets }: { status: TicketStatus; label: string; tickets: Ticket[] }) {
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
          <DraggableTicketCard key={ticket.id} ticket={ticket} />
        ))}
      </div>
    </div>
  )
}

export function KanbanBoard({ projectId, tickets }: { projectId: string; tickets: Ticket[] }) {
  const updateStatus = useUpdateTicketStatus(projectId)
  const [activeTicket, setActiveTicket] = useState<Ticket | null>(null)

  // Pointer needs a small movement threshold so a plain click still opens the
  // ticket link instead of always starting a drag; keyboard support (Tab to
  // the handle, Space to lift, arrows to move, Space to drop) comes for free.
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
          />
        ))}
      </div>
      <DragOverlay>{activeTicket && <TicketCardContent ticket={activeTicket} />}</DragOverlay>
    </DndContext>
  )
}
