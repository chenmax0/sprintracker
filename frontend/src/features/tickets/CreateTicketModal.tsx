import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useCreateTicket } from './hooks'

export function CreateTicketModal({
  projectId,
  activeSprintId,
  onClose,
}: {
  projectId: string
  activeSprintId: string | null
  onClose: () => void
}) {
  const createTicket = useCreateTicket(projectId)
  const [title, setTitle] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    createTicket.mutate({ title, sprintId: activeSprintId }, { onSuccess: onClose })
  }

  return (
    <Modal title="Nouveau ticket" onClose={onClose}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Titre</span>
          <input
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            autoFocus
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {createTicket.isError && (
          <p className="text-sm text-red-600">
            {createTicket.error instanceof ApiError ? createTicket.error.message : 'Erreur lors de la création'}
          </p>
        )}
        <button
          type="submit"
          disabled={createTicket.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Créer
        </button>
      </form>
    </Modal>
  )
}
