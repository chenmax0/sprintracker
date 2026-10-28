import { type FormEvent, useState } from 'react'
import { useNavigate } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { Modal } from '../../lib/Modal'
import type { Project } from './api'
import { useDeleteProject, useRenameProject } from './hooks'

export function ProjectSettingsModal({ project, onClose }: { project: Project; onClose: () => void }) {
  const navigate = useNavigate()
  const rename = useRenameProject(project.id)
  const deleteProjectMutation = useDeleteProject(project.id)
  const [name, setName] = useState(project.name)
  const [confirmText, setConfirmText] = useState('')

  function handleRename(event: FormEvent) {
    event.preventDefault()
    rename.mutate(name, { onSuccess: onClose })
  }

  function handleDelete(event: FormEvent) {
    event.preventDefault()
    deleteProjectMutation.mutate(undefined, { onSuccess: () => navigate('/app') })
  }

  return (
    <Modal title="Paramètres du projet" onClose={onClose}>
      <form onSubmit={handleRename} className="flex flex-col gap-3">
        <label className="flex flex-col gap-1">
          <span className="text-sm font-medium text-gray-700">Nom du projet</span>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
        </label>
        {rename.isError && (
          <p className="text-sm text-red-600">
            {rename.error instanceof ApiError ? rename.error.message : 'Erreur lors du renommage'}
          </p>
        )}
        <button
          type="submit"
          disabled={rename.isPending}
          className="self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Renommer
        </button>
      </form>

      <div className="mt-6 border-t border-gray-200 pt-4">
        <h3 className="mb-1 text-sm font-semibold text-red-600">Zone de danger</h3>
        <p className="mb-3 text-sm text-gray-600">
          Cette action supprime définitivement le projet, ses sprints et ses tickets. Tapez{' '}
          <span className="font-semibold text-gray-900">{project.name}</span> pour confirmer.
        </p>
        <form onSubmit={handleDelete} className="flex gap-2">
          <input
            type="text"
            value={confirmText}
            onChange={(e) => setConfirmText(e.target.value)}
            className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none"
          />
          <button
            type="submit"
            disabled={confirmText !== project.name || deleteProjectMutation.isPending}
            className="shrink-0 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 disabled:opacity-50"
          >
            Supprimer
          </button>
        </form>
        {deleteProjectMutation.isError && (
          <p className="mt-2 text-sm text-red-600">
            {deleteProjectMutation.error instanceof ApiError
              ? deleteProjectMutation.error.message
              : 'Erreur lors de la suppression'}
          </p>
        )}
      </div>
    </Modal>
  )
}
