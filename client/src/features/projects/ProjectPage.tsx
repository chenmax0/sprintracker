import { useState } from 'react'
import { Link, useParams } from 'react-router'
import { Card } from '../../lib/Card'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { BoardMenu } from '../board/BoardMenu'
import { CreateColumnModal } from '../board/CreateColumnModal'
import { useColumns } from '../board/hooks'
import { useSprints } from '../sprints/hooks'
import { SprintModal } from '../sprints/SprintModal'
import { useCompleteSprint } from '../sprints/hooks'
import { ConfirmModal } from '../../lib/ConfirmModal'
import { MembersModal } from '../teams/MembersModal'
import { useTeamMembers } from '../teams/hooks'
import { BacklogModal } from '../tickets/BacklogModal'
import { CreateTicketModal } from '../tickets/CreateTicketModal'
import { useTickets } from '../tickets/hooks'
import { ProjectKanbanBoard } from '../tickets/ProjectKanbanBoard'
import { TicketPanel } from '../tickets/TicketPanel'
import { useProject } from './hooks'
import { ProjectSettingsModal } from './ProjectSettingsModal'

export function ProjectPage() {
  const { projectId } = useParams<{ projectId: string }>()
  if (!projectId) throw new Error('Missing projectId')

  const { data: project } = useProject(projectId)
  const { data: sprints } = useSprints(projectId)
  const { data: columns } = useColumns(projectId)
  const { data: tickets } = useTickets(projectId)
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)

  const [showMembers, setShowMembers] = useState(false)
  const [showSettings, setShowSettings] = useState(false)
  const [showBacklog, setShowBacklog] = useState(false)
  const [showCreateTicket, setShowCreateTicket] = useState(false)
  const [showLaunchSprint, setShowLaunchSprint] = useState(false)
  const [showCompleteSprint, setShowCompleteSprint] = useState(false)
  const completeSprint = useCompleteSprint(projectId)
  const [selectedTicketId, setSelectedTicketId] = useState<string | null>(null)
  const [assigneeFilter, setAssigneeFilter] = useState('')
  const [editingColumns, setEditingColumns] = useState(false)
  const [showCreateColumn, setShowCreateColumn] = useState(false)

  const activeSprint = sprints?.find((sprint) => sprint.status === 'active') ?? null
  const sprintTickets = activeSprint ? (tickets ?? []).filter((ticket) => ticket.sprintId === activeSprint.id) : []
  // "Done" isn't a fixed status: it's whichever column is rightmost on the board.
  const lastColumnId = columns && columns.length > 0 ? columns[columns.length - 1].id : null
  const doneCount = sprintTickets.filter((ticket) => ticket.columnId === lastColumnId).length
  const progress = sprintTickets.length > 0 ? Math.round((doneCount / sprintTickets.length) * 100) : 0
  const boardTickets = assigneeFilter
    ? sprintTickets.filter((ticket) => ticket.assigneeId === assigneeFilter)
    : sprintTickets
  // Unfinished tickets of a completed sprint wait here until the next sprint is launched and carries them over.
  const completedSprintIds = new Set((sprints ?? []).filter((sprint) => sprint.status === 'completed').map((sprint) => sprint.id))
  const backlogTickets = (tickets ?? []).filter(
    (ticket) =>
      ticket.sprintId === null || (completedSprintIds.has(ticket.sprintId) && ticket.columnId !== lastColumnId),
  )

  return (
    <div className="flex flex-col gap-8">
      <div className="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <Link to="/app" className="text-sm text-indigo-600 hover:underline">
              ← Mes projets
            </Link>
            <div className="mt-2 flex items-center gap-3">
              <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-sm font-semibold text-indigo-700">
                {project?.name.slice(0, 2).toUpperCase()}
              </span>
              <h1 className="text-2xl font-semibold text-gray-900">{project?.name}</h1>
            </div>
          </div>
          <div className="flex gap-2">
            <button
              type="button"
              onClick={() => setShowBacklog(true)}
              className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
            >
              Backlog ({backlogTickets.length})
            </button>
            <button
              type="button"
              onClick={() => setShowMembers(true)}
              className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
            >
              Membres
            </button>
            {project?.isOwner && (
              <button
                type="button"
                onClick={() => setShowSettings(true)}
                className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
              >
                Paramètres
              </button>
            )}
          </div>
        </div>

        <Card>
          {activeSprint ? (
            <div className="flex flex-wrap items-center justify-between gap-4">
              <div className="min-w-48">
                <p className="text-sm font-semibold text-gray-900">{activeSprint.name}</p>
                <p className="text-xs text-gray-500">
                  {activeSprint.startDate} → {activeSprint.endDate}
                </p>
                <div className="mt-2 flex items-center gap-2">
                  <div className="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100">
                    <div className="h-full rounded-full bg-indigo-600 transition-all" style={{ width: `${progress}%` }} />
                  </div>
                  <span className="text-xs text-gray-500">
                    {doneCount}/{sprintTickets.length} terminés
                  </span>
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setShowCreateTicket(true)}
                  className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                  + Nouveau ticket
                </button>
                <button
                  type="button"
                  onClick={() => setShowCompleteSprint(true)}
                  className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                >
                  Terminer le sprint
                </button>
              </div>
            </div>
          ) : (
            <div className="flex flex-wrap items-center justify-between gap-4">
              <p className="text-sm text-gray-500">Aucun sprint en cours.</p>
              <button
                type="button"
                onClick={() => setShowLaunchSprint(true)}
                className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
              >
                Lancer un sprint
              </button>
            </div>
          )}
        </Card>
      </div>

      {activeSprint ? (
        <div className="flex flex-col gap-3">
          <div className="mx-auto flex w-full max-w-6xl items-center justify-between">
            <select
              value={assigneeFilter}
              onChange={(e) => setAssigneeFilter(e.target.value)}
              className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none"
            >
              <option value="">Tous les membres</option>
              {members?.map((member) => (
                <option key={member.memberId} value={member.memberId}>
                  {member.name}
                </option>
              ))}
            </select>
            <div className="flex items-center gap-2">
              {editingColumns && (
                <button
                  type="button"
                  onClick={() => setShowCreateColumn(true)}
                  className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                  + Créer une colonne
                </button>
              )}
              <BoardMenu isEditingColumns={editingColumns} onToggleEditColumns={() => setEditingColumns((v) => !v)} />
            </div>
          </div>
          <ProjectKanbanBoard
            projectId={projectId}
            tickets={boardTickets}
            memberDirectory={memberDirectory}
            onTicketClick={(ticket) => setSelectedTicketId(ticket.id)}
            editingColumns={editingColumns}
          />
        </div>
      ) : (
        <p className="text-center text-sm text-gray-400">Lancez un sprint pour voir apparaître le board.</p>
      )}

      {showMembers && project && <MembersModal teamId={project.teamId} onClose={() => setShowMembers(false)} />}
      {showCreateTicket && (
        <CreateTicketModal
          projectId={projectId}
          activeSprintId={activeSprint?.id ?? null}
          members={members ?? []}
          onClose={() => setShowCreateTicket(false)}
        />
      )}
      {showLaunchSprint && <SprintModal projectId={projectId} onClose={() => setShowLaunchSprint(false)} />}
      {showCompleteSprint && activeSprint && (
        <ConfirmModal
          title="Terminer le sprint"
          message="Le sprint sera clôturé. Les tickets non terminés restent en attente et seront reportés sur le prochain sprint, que vous lancerez vous-même."
          confirmLabel="Terminer le sprint"
          tone="primary"
          isPending={completeSprint.isPending}
          errorMessage={completeSprint.isError ? 'Erreur lors de cette action' : null}
          onConfirm={() => completeSprint.mutate(activeSprint.id, { onSuccess: () => setShowCompleteSprint(false) })}
          onClose={() => setShowCompleteSprint(false)}
        />
      )}
      {selectedTicketId && <TicketPanel ticketId={selectedTicketId} onClose={() => setSelectedTicketId(null)} />}
      {showSettings && project && <ProjectSettingsModal project={project} onClose={() => setShowSettings(false)} />}
      {showBacklog && (
        <BacklogModal
          projectId={projectId}
          tickets={backlogTickets}
          activeSprintId={activeSprint?.id ?? null}
          onClose={() => setShowBacklog(false)}
        />
      )}
      {showCreateColumn && <CreateColumnModal projectId={projectId} onClose={() => setShowCreateColumn(false)} />}
    </div>
  )
}
