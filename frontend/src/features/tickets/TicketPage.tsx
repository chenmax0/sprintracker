import { type FormEvent, useState } from 'react'
import { Link, useParams } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { MemberLabel } from '../../lib/MemberLabel'
import { useAddComment, useAssignTicket, useComments, useTicket } from './hooks'

export function TicketPage() {
  const { ticketId } = useParams<{ ticketId: string }>()
  if (!ticketId) throw new Error('Missing ticketId')

  const { data: ticket } = useTicket(ticketId)
  const assign = useAssignTicket(ticketId)
  const { data: comments } = useComments(ticketId)
  const addComment = useAddComment(ticketId)

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
    <div className="flex flex-col gap-8">
      <div>
        {ticket && (
          <Link to={`/projects/${ticket.projectId}`} className="text-sm text-indigo-600 hover:underline">
            ← Projet
          </Link>
        )}
        <h1 className="mt-2 text-xl font-semibold text-gray-900">{ticket?.title}</h1>
        {ticket?.description && <p className="mt-1 text-sm text-gray-600">{ticket.description}</p>}
      </div>

      <section className="flex flex-wrap gap-6 rounded-md border border-gray-200 bg-white p-4 text-sm">
        <div>
          <span className="block text-xs text-gray-500">Statut</span>
          <span className="font-medium text-gray-900">{ticket?.status}</span>
        </div>
        <div>
          <span className="block text-xs text-gray-500">Rapporté par</span>
          {ticket && <MemberLabel memberId={ticket.reporterId} />}
        </div>
        <div>
          <span className="block text-xs text-gray-500">Assigné à</span>
          {ticket?.assigneeId ? <MemberLabel memberId={ticket.assigneeId} /> : <span className="text-gray-400">Personne</span>}
        </div>
      </section>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Réassigner</h2>
        <form onSubmit={handleAssign} className="flex max-w-sm gap-2">
          <input
            type="text"
            value={assigneeId}
            onChange={(e) => setAssigneeId(e.target.value)}
            placeholder="ID du membre (vide = désassigner)"
            className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
          <button
            type="submit"
            disabled={assign.isPending}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Assigner
          </button>
        </form>
        {assign.isError && (
          <p className="mt-2 text-sm text-red-600">
            {assign.error instanceof ApiError ? assign.error.message : "Erreur lors de l'assignation"}
          </p>
        )}
      </section>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Commentaires</h2>
        <ul className="mb-4 flex flex-col gap-3">
          {comments?.map((comment) => (
            <li key={comment.id} className="rounded-md border border-gray-200 bg-white p-3 text-sm">
              <div className="mb-1 flex items-center gap-2 text-xs text-gray-500">
                <MemberLabel memberId={comment.authorId} />
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
      </section>
    </div>
  )
}
