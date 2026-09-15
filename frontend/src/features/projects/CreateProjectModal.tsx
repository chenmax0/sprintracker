import { type FormEvent, useState } from 'react'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import { useCreateMyProject } from './hooks'

export function CreateProjectModal({ onClose }: { onClose: () => void }) {
  const createProject = useCreateMyProject()
  const [name, setName] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    createProject.mutate(name, { onSuccess: onClose })
  }

  return (
    <Modal title="Nouveau projet" onClose={onClose}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Nom du projet</span>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            autoFocus
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {createProject.isError && (
          <p className="text-sm text-red-600">
            {createProject.error instanceof ApiError ? createProject.error.message : 'Erreur lors de la création'}
          </p>
        )}
        <button
          type="submit"
          disabled={createProject.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Créer
        </button>
      </form>
    </Modal>
  )
}
