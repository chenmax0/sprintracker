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
import { arrayMove, horizontalListSortingStrategy, SortableContext, useSortable } from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import { type CSSProperties, useState } from 'react'
import { Link } from 'react-router'
import { MemberLabel } from '../../lib/MemberLabel'
import type { MemberDirectory } from '../../lib/memberDirectory'
import type { Column } from '../board/api'
import type { Ticket } from './api'

const columnSortId = (columnId: string) => `column:${columnId}`

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
          <span className="mr-1 font-normal text-gray-400">#{ticket.number}</span>
          {ticket.title}
        </Link>
      ) : onTicketClick ? (
        <button
          type="button"
          onClick={() => onTicketClick(ticket)}
          className="text-left font-medium text-gray-900 hover:underline"
        >
          <span className="mr-1 font-normal text-gray-400">#{ticket.number}</span>
          {ticket.title}
        </button>
      ) : (
        <span className="font-medium text-gray-900">
          <span className="mr-1 font-normal text-gray-400">#{ticket.number}</span>
          {ticket.title}
        </span>
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

interface ColumnBodyProps {
  column: Column
  tickets: Ticket[]
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
  reorderable: boolean
}

function ColumnBody({ column, tickets, memberDirectory, ticketHref, onTicketClick, reorderable }: ColumnBodyProps) {
  const { setNodeRef: setDropRef, isOver } = useDroppable({ id: column.id })
  const {
    attributes,
    listeners,
    setNodeRef: setSortRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: columnSortId(column.id), disabled: !reorderable })

  const style: CSSProperties = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  }

  return (
    <div ref={setSortRef} style={style} className="flex w-80 shrink-0 flex-col gap-2">
      <h3
        {...(reorderable ? { ...attributes, ...listeners } : {})}
        className={`truncate text-xs font-semibold tracking-wide text-gray-500 uppercase ${reorderable ? 'cursor-grab touch-none' : ''}`}
        title={reorderable ? 'Maintenir pour réordonner' : undefined}
      >
        {column.name} <span className="text-gray-400">({tickets.length})</span>
      </h3>
      <div
        ref={setDropRef}
        className={`flex min-h-16 flex-1 flex-col gap-2 rounded-md border p-3 transition-colors ${
          isOver ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 bg-gray-100'
        }`}
      >
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
  columns: Column[]
  tickets: Ticket[]
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
  onTicketMove: (ticketId: string, columnId: string) => void
  errorMessage?: string | null
  onReorderColumns?: (columnIds: string[]) => void
}

/**
 * Presentational drag-and-drop board: it only renders tickets/columns and
 * reports changes (a ticket dropped on a column, a column reordered)
 * upward. Whether those changes are persisted (real projects) or kept
 * local-only (read-only demo) is entirely up to the caller - column
 * reordering only activates when onReorderColumns is provided. Creating,
 * renaming and deleting columns lives in ColumnManagerMenu, not here.
 */
export function KanbanBoard({
  columns,
  tickets,
  memberDirectory,
  ticketHref,
  onTicketClick,
  onTicketMove,
  errorMessage,
  onReorderColumns,
}: KanbanBoardProps) {
  const [activeTicket, setActiveTicket] = useState<Ticket | null>(null)
  const reorderable = onReorderColumns !== undefined

  // The whole ticket card is its drag surface, and a column is dragged by
  // its header, so pointer needs a movement threshold: a plain click/tap
  // still opens the ticket link, only a hold-and-move past 8px starts a drag.
  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 8 } }),
    useSensor(KeyboardSensor),
  )

  function handleDragStart(event: DragStartEvent) {
    const activeId = String(event.active.id)
    if (!activeId.startsWith('column:')) {
      setActiveTicket(tickets.find((ticket) => ticket.id === activeId) ?? null)
    }
  }

  function handleDragEnd(event: DragEndEvent) {
    setActiveTicket(null)

    const { active, over } = event
    if (!over) {
      return
    }

    const activeId = String(active.id)

    if (activeId.startsWith('column:')) {
      const overId = String(over.id)
      if (!onReorderColumns || !overId.startsWith('column:')) {
        return
      }

      const oldIndex = columns.findIndex((column) => columnSortId(column.id) === activeId)
      const newIndex = columns.findIndex((column) => columnSortId(column.id) === overId)
      if (oldIndex === -1 || newIndex === -1 || oldIndex === newIndex) {
        return
      }

      onReorderColumns(arrayMove(columns, oldIndex, newIndex).map((column) => column.id))
      return
    }

    const newColumnId = String(over.id)
    const ticket = tickets.find((t) => t.id === activeId)

    if (ticket && ticket.columnId !== newColumnId) {
      onTicketMove(ticket.id, newColumnId)
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
          <SortableContext items={columns.map((column) => columnSortId(column.id))} strategy={horizontalListSortingStrategy}>
            {columns.map((column) => (
              <ColumnBody
                key={column.id}
                column={column}
                tickets={tickets.filter((ticket) => ticket.columnId === column.id)}
                memberDirectory={memberDirectory}
                ticketHref={ticketHref}
                onTicketClick={onTicketClick}
                reorderable={reorderable}
              />
            ))}
          </SortableContext>
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
