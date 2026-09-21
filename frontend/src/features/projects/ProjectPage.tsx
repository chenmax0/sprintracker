import { useState } from 'react'
import { Link, useParams } from 'react-router'
import { Card } from '../../lib/Card'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { useSprints } from '../sprints/hooks'
import { SprintModal } from '../sprints/SprintModal'
import { MembersModal } from '../teams/MembersModal'
import { useTeamMembers } from '../teams/hooks'
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
  const { data: tickets } = useTickets(projectId)
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)

  const [showMembers, setShowMembers] = useState(false)
  const [showSettings, setShowSettings] = useState(false)
  const [showCreateTicket, setShowCreateTicket] = useState(false)
  const [sprintModal, setSprintModal] = useState<'launch' | 'complete' | null>(null)
  const [selectedTicketId, setSelectedTicketId] = useState<string | null>(null)
  const [assigneeFilter, setAssigneeFilter] = useState('')

  const activeSprint = sprints?.find((sprint) => sprint.status === 'active') ?? null
  const sprintTickets = activeSprint ? (tickets ?? []).filter((ticket) => ticket.sprintId === activeSprint.id) : []
  const doneCount = sprintTickets.filter((ticket) => ticket.status === 'done').length
  const progress = sprintTickets.length > 0 ? Math.round((doneCount / sprintTickets.length) * 100) : 0
  const boardTickets = assigneeFilter
    ? sprintTickets.filter((ticket) => ticket.assigneeId === assigneeFilter)
    : sprintTickets

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
                  onClick={() => setSprintModal('complete')}
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
                onClick={() => setSprintModal('launch')}
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
          <div className="flex justify-center">
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
          </div>
          <ProjectKanbanBoard
            projectId={projectId}
            tickets={boardTickets}
            memberDirectory={memberDirectory}
            onTicketClick={(ticket) => setSelectedTicketId(ticket.id)}
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
      {sprintModal && (
        <SprintModal
          projectId={projectId}
          completingSprintId={sprintModal === 'complete' ? (activeSprint?.id ?? undefined) : undefined}
          onClose={() => setSprintModal(null)}
        />
      )}
      {selectedTicketId && <TicketPanel ticketId={selectedTicketId} onClose={() => setSelectedTicketId(null)} />}
      {showSettings && project && <ProjectSettingsModal project={project} onClose={() => setShowSettings(false)} />}
    </div>
  )
}
