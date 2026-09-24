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
import { type CSSProperties, type FormEvent, useState } from 'react'
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

interface ColumnHeaderProps {
  name: string
  ticketCount: number
  editable: boolean
  onRename?: (name: string) => void
  onDelete?: () => void
  dragHandleProps?: Record<string, unknown>
}

function ColumnHeader({ name, ticketCount, editable, onRename, onDelete, dragHandleProps }: ColumnHeaderProps) {
  const [isEditing, setIsEditing] = useState(false)
  const [draftName, setDraftName] = useState(name)

  function commitRename(event: FormEvent) {
    event.preventDefault()
    setIsEditing(false)
    if (draftName.trim() && draftName !== name) {
      onRename?.(draftName.trim())
    } else {
      setDraftName(name)
    }
  }

  if (isEditing) {
    return (
      <form onSubmit={commitRename}>
        <input
          autoFocus
          value={draftName}
          onChange={(e) => setDraftName(e.target.value)}
          onBlur={commitRename}
          className="w-full rounded border border-indigo-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-gray-700"
        />
      </form>
    )
  }

  return (
    <div className="flex items-center justify-between gap-2">
      <h3
        {...dragHandleProps}
        onClick={editable ? () => setIsEditing(true) : undefined}
        className={`truncate text-xs font-semibold tracking-wide text-gray-500 uppercase ${editable ? 'cursor-pointer' : ''}`}
        title={editable ? 'Cliquer pour renommer, maintenir pour réordonner' : undefined}
      >
        {name} <span className="text-gray-400">({ticketCount})</span>
      </h3>
      {editable && (
        <button
          type="button"
          onClick={onDelete}
          aria-label={`Supprimer la colonne "${name}"`}
          className="shrink-0 text-gray-300 hover:text-red-500"
        >
          ✕
        </button>
      )}
    </div>
  )
}

interface ColumnBodyProps {
  column: Column
  tickets: Ticket[]
  memberDirectory?: MemberDirectory
  ticketHref?: (ticketId: string) => string
  onTicketClick?: (ticket: Ticket) => void
  editable: boolean
  onRenameColumn?: (columnId: string, name: string) => void
  onDeleteColumn?: (columnId: string) => void
}

function ColumnBody({
  column,
  tickets,
  memberDirectory,
  ticketHref,
  onTicketClick,
  editable,
  onRenameColumn,
  onDeleteColumn,
}: ColumnBodyProps) {
  const { setNodeRef: setDropRef, isOver } = useDroppable({ id: column.id })
  const {
    attributes,
    listeners,
    setNodeRef: setSortRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: columnSortId(column.id), disabled: !editable })

  const style: CSSProperties = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  }

  return (
    <div ref={setSortRef} style={style} className="flex w-80 shrink-0 flex-col gap-2">
      <ColumnHeader
        name={column.name}
        ticketCount={tickets.length}
        editable={editable}
        onRename={(name) => onRenameColumn?.(column.id, name)}
        onDelete={() => onDeleteColumn?.(column.id)}
        dragHandleProps={editable ? { ...attributes, ...listeners } : undefined}
      />
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

function NewColumnButton({ onCreate }: { onCreate: (name: string) => void }) {
  const [isEditing, setIsEditing] = useState(false)
  const [name, setName] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    if (name.trim()) {
      onCreate(name.trim())
      setName('')
    }
    setIsEditing(false)
  }

  if (isEditing) {
    return (
      <form onSubmit={handleSubmit} className="w-80 shrink-0">
        <input
          autoFocus
          value={name}
          onChange={(e) => setName(e.target.value)}
          onBlur={handleSubmit}
          placeholder="Nom de la colonne"
          className="w-full rounded-md border border-indigo-300 px-3 py-2 text-sm focus:outline-none"
        />
      </form>
    )
  }

  return (
    <button
      type="button"
      onClick={() => setIsEditing(true)}
      className="h-9 w-80 shrink-0 rounded-md border border-dashed border-gray-300 text-sm text-gray-400 hover:border-gray-400 hover:text-gray-600"
    >
      + Colonne
    </button>
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
  onCreateColumn?: (name: string) => void
  onRenameColumn?: (columnId: string, name: string) => void
  onReorderColumns?: (columnIds: string[]) => void
  onDeleteColumn?: (columnId: string) => void
}

/**
 * Presentational drag-and-drop board: it only renders tickets/columns and
 * reports changes (a ticket dropped on a column, a column reordered)
 * upward. Whether those changes are persisted (real projects) or kept
 * local-only (read-only demo) is entirely up to the caller - column
 * editing (create/rename/reorder/delete) only renders when the matching
 * handler prop is provided.
 */
export function KanbanBoard({
  columns,
  tickets,
  memberDirectory,
  ticketHref,
  onTicketClick,
  onTicketMove,
  errorMessage,
  onCreateColumn,
  onRenameColumn,
  onReorderColumns,
  onDeleteColumn,
}: KanbanBoardProps) {
  const [activeTicket, setActiveTicket] = useState<Ticket | null>(null)
  const editable = onReorderColumns !== undefined

  // The whole ticket card is its drag surface, and a column is dragged by
  // its header, so pointer needs a movement threshold: a plain click/tap
  // still opens the ticket link or starts renaming, only a hold-and-move
  // past 8px starts a drag.
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
                editable={editable}
                onRenameColumn={onRenameColumn}
                onDeleteColumn={onDeleteColumn}
              />
            ))}
          </SortableContext>
          {onCreateColumn && <NewColumnButton onCreate={onCreateColumn} />}
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
