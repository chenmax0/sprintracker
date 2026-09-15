import { type FormEvent, useState } from 'react'
import { Link, useParams } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { Card } from '../../lib/Card'
import { MemberLabel } from '../../lib/MemberLabel'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { useProject } from '../projects/hooks'
import { useSprints } from '../sprints/hooks'
import { useTeamMembers } from '../teams/hooks'
import type { TicketStatus } from './api'
import { useAddComment, useAssignTicket, useComments, useTicket, useTicketSprintHistory } from './hooks'

const STATUS_LABELS: Record<TicketStatus, string> = {
  todo: 'À faire',
  in_progress: 'En cours',
  done: 'Terminé',
}

const STATUS_STYLES: Record<TicketStatus, string> = {
  todo: 'bg-gray-100 text-gray-700',
  in_progress: 'bg-amber-100 text-amber-700',
  done: 'bg-emerald-100 text-emerald-700',
}

export function TicketPage() {
  const { ticketId } = useParams<{ ticketId: string }>()
  if (!ticketId) throw new Error('Missing ticketId')

  const { data: ticket } = useTicket(ticketId)
  const { data: project } = useProject(ticket?.projectId ?? '')
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)
  const assign = useAssignTicket(ticketId)
  const { data: comments } = useComments(ticketId)
  const addComment = useAddComment(ticketId)
  const { data: sprints } = useSprints(ticket?.projectId ?? '')
  const { data: sprintHistory } = useTicketSprintHistory(ticketId)
  const sprintNames = new Map((sprints ?? []).map((sprint) => [sprint.id, sprint.name]))

  const [assigneeId, setAssigneeId] = useState('')
  const [content, setContent] = useState('')

  function handleAssign(event: FormEvent) {
    event.preventDefault()
    assign.mutate(assigneeId || null, { onSuccess: () => setAssigneeId('') })
  }

  function handleAddComment(event: FormEvent) {
    event.preventDefault()
    addComment.mutate(content, { onSuccess: () => setContent('') })
  }

  return (
    <div className="mx-auto flex max-w-4xl flex-col gap-6">
      <div>
        {ticket && (
          <Link to={`/projects/${ticket.projectId}`} className="text-sm text-indigo-600 hover:underline">
            ← Projet
          </Link>
        )}
        <h1 className="mt-2 text-2xl font-semibold text-gray-900">{ticket?.title}</h1>
        {ticket?.description && <p className="mt-2 text-sm text-gray-600">{ticket.description}</p>}
      </div>

      <Card>
        <div className="flex flex-wrap gap-8 text-sm">
          <div>
            <span className="block text-xs text-gray-500">Statut</span>
            {ticket && (
              <span className={`mt-1 inline-block rounded px-2 py-0.5 text-xs font-medium ${STATUS_STYLES[ticket.status]}`}>
                {STATUS_LABELS[ticket.status]}
              </span>
            )}
          </div>
          <div>
            <span className="block text-xs text-gray-500">Rapporté par</span>
            {ticket && <MemberLabel memberId={ticket.reporterId} directory={memberDirectory} />}
          </div>
          <div>
            <span className="block text-xs text-gray-500">Assigné à</span>
            {ticket?.assigneeId ? (
              <MemberLabel memberId={ticket.assigneeId} directory={memberDirectory} />
            ) : (
              <span className="text-gray-400">Personne</span>
            )}
          </div>
        </div>
      </Card>

      {sprintHistory && sprintHistory.length > 0 && (
        <Card title="Historique">
          <p className="mb-3 text-sm text-gray-600">
            Ce ticket a été reporté depuis {sprintHistory.length} sprint{sprintHistory.length > 1 ? 's' : ''} précédent
            {sprintHistory.length > 1 ? 's' : ''}.
          </p>
          <ol className="flex flex-wrap items-center gap-2 text-sm text-gray-700">
            {sprintHistory.map((entry, index) => (
              <li key={entry.sprintId} className="flex items-center gap-2">
                <span className="rounded bg-gray-100 px-2 py-0.5">
                  {sprintNames.get(entry.sprintId) ?? entry.sprintId}
                </span>
                {index < sprintHistory.length - 1 && <span className="text-gray-300">→</span>}
              </li>
            ))}
          </ol>
        </Card>
      )}

      <Card title="Réassigner">
        <form onSubmit={handleAssign} className="flex max-w-sm gap-2">
          <select
            value={assigneeId}
            onChange={(e) => setAssigneeId(e.target.value)}
            className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          >
            <option value="">Personne</option>
            {members?.map((member) => (
              <option key={member.memberId} value={member.memberId}>
                {member.name}
              </option>
            ))}
          </select>
          <button
            type="submit"
            disabled={assign.isPending}
            className="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Assigner
          </button>
        </form>
        {assign.isError && (
          <p className="mt-2 text-sm text-red-600">
            {assign.error instanceof ApiError ? assign.error.message : "Erreur lors de l'assignation"}
          </p>
        )}
      </Card>

      <Card title="Commentaires">
        <ul className="mb-4 flex flex-col gap-3">
          {comments?.map((comment) => (
            <li key={comment.id} className="rounded-md border border-gray-200 p-3 text-sm">
              <div className="mb-1 flex items-center gap-2 text-xs text-gray-500">
                <MemberLabel memberId={comment.authorId} directory={memberDirectory} />
                <span>{new Date(comment.createdAt).toLocaleString()}</span>
              </div>
              <p className="text-gray-800">{comment.content}</p>
            </li>
          ))}
          {comments?.length === 0 && <p className="text-sm text-gray-500">Aucun commentaire.</p>}
        </ul>
        <form onSubmit={handleAddComment} className="flex flex-col gap-2">
          <textarea
            value={content}
            onChange={(e) => setContent(e.target.value)}
            required
            rows={3}
            placeholder="Ajouter un commentaire…"
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
          <button
            type="submit"
            disabled={addComment.isPending}
            className="self-start rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Commenter
          </button>
        </form>
      </Card>
    </div>
  )
}
