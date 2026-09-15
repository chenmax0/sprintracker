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
import { useProject } from './hooks'

export function ProjectPage() {
  const { projectId } = useParams<{ projectId: string }>()
  if (!projectId) throw new Error('Missing projectId')

  const { data: project } = useProject(projectId)
  const { data: sprints } = useSprints(projectId)
  const { data: tickets } = useTickets(projectId)
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)

  const [showMembers, setShowMembers] = useState(false)
  const [showCreateTicket, setShowCreateTicket] = useState(false)
  const [sprintModal, setSprintModal] = useState<'launch' | 'complete' | null>(null)

  const activeSprint = sprints?.find((sprint) => sprint.status === 'active') ?? null
  const sprintTickets = activeSprint ? (tickets ?? []).filter((ticket) => ticket.sprintId === activeSprint.id) : []

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
          <button
            type="button"
            onClick={() => setShowMembers(true)}
            className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
          >
            Membres
          </button>
        </div>

        <Card>
          {activeSprint ? (
            <div className="flex flex-wrap items-center justify-between gap-4">
              <div>
                <p className="text-sm font-semibold text-gray-900">{activeSprint.name}</p>
                <p className="text-xs text-gray-500">
                  {activeSprint.startDate} → {activeSprint.endDate}
                </p>
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
        <ProjectKanbanBoard projectId={projectId} tickets={sprintTickets} memberDirectory={memberDirectory} />
      ) : (
        <p className="text-center text-sm text-gray-400">Lancez un sprint pour voir apparaître le board.</p>
      )}

      {showMembers && project && <MembersModal teamId={project.teamId} onClose={() => setShowMembers(false)} />}
      {showCreateTicket && (
        <CreateTicketModal
          projectId={projectId}
          activeSprintId={activeSprint?.id ?? null}
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
    </div>
  )
}
