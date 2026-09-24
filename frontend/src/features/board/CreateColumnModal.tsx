import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useCreateColumn } from './hooks'

export function CreateColumnModal({ projectId, onClose }: { projectId: string; onClose: () => void }) {
  const createColumn = useCreateColumn(projectId)
  const [name, setName] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    createColumn.mutate(name, { onSuccess: onClose })
  }

  return (
    <Modal title="Nouvelle colonne" onClose={onClose}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Nom de la colonne</span>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            autoFocus
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {createColumn.isError && (
          <p className="text-sm text-red-600">
            {createColumn.error instanceof ApiError ? createColumn.error.message : 'Erreur lors de la création'}
          </p>
        )}
        <button
          type="submit"
          disabled={createColumn.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Créer
        </button>
      </form>
    </Modal>
  )
}
