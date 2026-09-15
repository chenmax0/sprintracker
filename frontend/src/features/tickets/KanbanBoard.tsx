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
import { MemberLabel } from '../../lib/MemberLabel'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Ticket, TicketStatus } from './api'

const COLUMNS: { status: TicketStatus; label: string }[] = [
  { status: 'todo', label: 'À faire' },
  { status: 'in_progress', label: 'En cours' },
  { status: 'done', label: 'Terminé' },
]

interface TicketCardContentProps {
  ticket: Ticket
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
}

function TicketCardContent({ ticket, memberDirectory, ticketHref, onTicketClick }: TicketCardContentProps) {
  return (
    <div className="rounded-md border border-gray-200 bg-white p-3 text-sm shadow-sm">
      {ticketHref ? (
        <Link to={ticketHref(ticket.id)} className="font-medium text-gray-900 hover:underline">
          {ticket.title}
        </Link>
      ) : onTicketClick ? (
        <button
          type="button"
          onClick={() => onTicketClick(ticket)}
          className="text-left font-medium text-gray-900 hover:underline"
        >
          {ticket.title}
        </button>
      ) : (
        <span className="font-medium text-gray-900">{ticket.title}</span>
      )}
      <div className="mt-1 flex items-center justify-between text-xs text-gray-400">
        {ticket.carriedOverCount > 0 ? (
          <span
            className="rounded bg-amber-100 px-1.5 py-0.5 font-medium text-amber-700"
            title={`Reporté depuis ${ticket.carriedOverCount} sprint(s)`}
          >
            ↻ {ticket.carriedOverCount}
          </span>
        ) : (
          <span />
        )}
        {ticket.assigneeId && (
          <span className="text-gray-500">
            <MemberLabel memberId={ticket.assigneeId} directory={memberDirectory} />
          </span>
        )}
      </div>
    </div>
  )
}

interface DraggableTicketCardProps {
  ticket: Ticket
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
}

function DraggableTicketCard({ ticket, memberDirectory, ticketHref, onTicketClick }: DraggableTicketCardProps) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({ id: ticket.id })

  const style: CSSProperties = {
    transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
    opacity: isDragging ? 0.4 : 1,
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      {...listeners}
      {...attributes}
      className="cursor-grab touch-none active:cursor-grabbing"
      aria-label={`Ticket "${ticket.title}", maintenir pour déplacer`}
    >
      <TicketCardContent ticket={ticket} memberDirectory={memberDirectory} ticketHref={ticketHref} onTicketClick={onTicketClick} />
    </div>
  )
}

interface ColumnProps {
  status: TicketStatus
  label: string
  tickets: Ticket[]
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
}

function Column({ status, label, tickets, memberDirectory, ticketHref, onTicketClick }: ColumnProps) {
  const { setNodeRef, isOver } = useDroppable({ id: status })

  return (
    <div
      ref={setNodeRef}
      className={`flex w-80 shrink-0 flex-col gap-2 rounded-md border p-3 transition-colors ${
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
            memberDirectory={memberDirectory}
            ticketHref={ticketHref}
            onTicketClick={onTicketClick}
          />
        ))}
      </div>
    </div>
  )
}

interface KanbanBoardProps {
  tickets: Ticket[]
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
  onStatusChange: (ticketId: string, status: TicketStatus) => void
  errorMessage?: string | null
}

/**
 * Presentational drag-and-drop board: it only renders tickets and reports a
 * drop as a (ticketId, status) pair via onStatusChange. Whether that change
 * is persisted (real projects) or kept in local-only state (read-only demo)
 * is entirely up to the caller.
 */
export function KanbanBoard({ tickets, memberDirectory, ticketHref, onTicketClick, onStatusChange, errorMessage }: KanbanBoardProps) {
  const [activeTicket, setActiveTicket] = useState<Ticket | null>(null)

  // The whole card is the drag surface (see DraggableTicketCard), so pointer
  // needs a movement threshold: a plain click/tap without holding still opens
  // the ticket link, and only a hold-and-move past 8px starts a drag.
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
      onStatusChange(ticket.id, newStatus)
    }
  }

  return (
    <div>
      {errorMessage && <p className="mb-2 text-sm text-red-600">{errorMessage}</p>}
      <DndContext
        sensors={sensors}
        collisionDetection={closestCenter}
        onDragStart={handleDragStart}
        onDragEnd={handleDragEnd}
      >
        <div className="flex justify-center gap-4 overflow-x-auto pb-2">
          {COLUMNS.map((column) => (
            <Column
              key={column.status}
              status={column.status}
              label={column.label}
              tickets={tickets.filter((ticket) => ticket.status === column.status)}
              memberDirectory={memberDirectory}
              ticketHref={ticketHref}
              onTicketClick={onTicketClick}
            />
          ))}
        </div>
        <DragOverlay>
          {activeTicket && (
            <TicketCardContent ticket={activeTicket} memberDirectory={memberDirectory} ticketHref={ticketHref} />
          )}
        </DragOverlay>
      </DndContext>
    </div>
  )
}
