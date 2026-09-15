import { type FormEvent, useState } from 'react'
import { Link, useParams } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { useCreateSprint, useSprints } from '../sprints/hooks'
import { useTeamMembers } from '../teams/hooks'
import { KanbanBoard } from '../tickets/KanbanBoard'
import { useCreateTicket, useTickets } from '../tickets/hooks'
import { useProject } from './hooks'

export function ProjectPage() {
  const { projectId } = useParams<{ projectId: string }>()
  if (!projectId) throw new Error('Missing projectId')

  const { data: project } = useProject(projectId)
  const { data: sprints } = useSprints(projectId)
  const createSprint = useCreateSprint(projectId)
  const { data: tickets } = useTickets(projectId)
  const createTicket = useCreateTicket(projectId)
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)

  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [ticketTitle, setTicketTitle] = useState('')
  const [ticketSprintId, setTicketSprintId] = useState('')

  function handleCreateSprint(event: FormEvent) {
    event.preventDefault()
    createSprint.mutate(
      { startDate, endDate },
      { onSuccess: () => { setStartDate(''); setEndDate('') } },
    )
  }

  function handleCreateTicket(event: FormEvent) {
    event.preventDefault()
    createTicket.mutate(
      { title: ticketTitle, sprintId: ticketSprintId || null },
      { onSuccess: () => setTicketTitle('') },
    )
  }

  return (
    <div className="flex flex-col gap-10">
      <div>
        {project && (
          <Link to={`/teams/${project.teamId}`} className="text-sm text-indigo-600 hover:underline">
            ← Équipe
          </Link>
        )}
        <h1 className="mt-2 text-xl font-semibold text-gray-900">{project?.name}</h1>
      </div>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Sprints</h2>
        <ul className="mb-3 flex flex-col gap-2">
          {sprints?.map((sprint) => (
            <li key={sprint.id} className="rounded-md border border-gray-200 bg-white px-4 py-3 text-sm">
              <span className="font-medium text-gray-900">{sprint.name}</span>{' '}
              <span className="text-gray-500">
                ({sprint.startDate} → {sprint.endDate})
              </span>
            </li>
          ))}
          {sprints?.length === 0 && <p className="text-sm text-gray-500">Aucun sprint pour l'instant.</p>}
        </ul>
        <form onSubmit={handleCreateSprint} className="flex max-w-md flex-wrap items-end gap-2">
          <label className="flex flex-col gap-1">
            <span className="text-xs text-gray-500">Début</span>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
          <label className="flex flex-col gap-1">
            <span className="text-xs text-gray-500">Fin</span>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
          <button
            type="submit"
            disabled={createSprint.isPending}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Créer le sprint
          </button>
        </form>
        {createSprint.isError && (
          <p className="mt-2 text-sm text-red-600">
            {createSprint.error instanceof ApiError ? createSprint.error.message : 'Erreur lors de la création'}
          </p>
        )}
      </section>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Tickets</h2>
        <form onSubmit={handleCreateTicket} className="mb-4 flex max-w-lg flex-wrap items-end gap-2">
          <label className="flex flex-1 flex-col gap-1">
            <span className="text-xs text-gray-500">Titre</span>
            <input
              type="text"
              value={ticketTitle}
              onChange={(e) => setTicketTitle(e.target.value)}
              required
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
          </label>
          <label className="flex flex-col gap-1">
            <span className="text-xs text-gray-500">Sprint (optionnel)</span>
            <select
              value={ticketSprintId}
              onChange={(e) => setTicketSprintId(e.target.value)}
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            >
              <option value="">Backlog</option>
              {sprints?.map((sprint) => (
                <option key={sprint.id} value={sprint.id}>
                  {sprint.name}
                </option>
              ))}
            </select>
          </label>
          <button
            type="submit"
            disabled={createTicket.isPending}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Créer le ticket
          </button>
        </form>
        {createTicket.isError && (
          <p className="mb-4 text-sm text-red-600">
            {createTicket.error instanceof ApiError ? createTicket.error.message : 'Erreur lors de la création'}
          </p>
        )}

        {tickets && <KanbanBoard projectId={projectId} tickets={tickets} sprints={sprints} memberDirectory={memberDirectory} />}
      </section>
    </div>
  )
}
