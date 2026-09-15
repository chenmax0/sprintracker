import { type FormEvent, useState } from 'react'
import { Link } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { Card } from '../../lib/Card'
import { useCreateMyProject, useMyProjects } from './hooks'

export function MyProjectsPage() {
  const { data: projects, isLoading } = useMyProjects()
  const createProject = useCreateMyProject()
  const [name, setName] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    createProject.mutate(name, { onSuccess: () => setName('') })
  }

  return (
    <div className="mx-auto flex max-w-6xl flex-col gap-8">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-gray-900">Mes projets</h1>
          <p className="mt-1 text-sm text-gray-500">Retrouvez ici tous les projets auxquels vous participez.</p>
        </div>
        <form onSubmit={handleSubmit} className="flex gap-2">
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Nom du nouveau projet"
            required
            className="w-64 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
          <button
            type="submit"
            disabled={createProject.isPending}
            className="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Nouveau projet
          </button>
        </form>
      </div>
      {createProject.isError && (
        <p className="-mt-4 self-end text-sm text-red-600">
          {createProject.error instanceof ApiError ? createProject.error.message : 'Erreur lors de la création'}
        </p>
      )}

      {isLoading && <p className="text-sm text-gray-500">Chargement…</p>}

      {projects?.length === 0 && (
        <Card className="border-dashed text-center">
          <p className="text-sm text-gray-500">
            Aucun projet pour l'instant. Créez-en un pour commencer à suivre vos tickets.
          </p>
        </Card>
      )}

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {projects?.map((project) => (
          <Link
            key={project.id}
            to={`/projects/${project.id}`}
            className="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-300 hover:shadow-md"
          >
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-sm font-semibold text-indigo-700">
              {project.name.slice(0, 2).toUpperCase()}
            </span>
            <div className="min-w-0">
              <p className="truncate text-sm font-medium text-gray-900">{project.name}</p>
              <p className="truncate text-xs text-gray-500">{project.teamName}</p>
            </div>
          </Link>
        ))}
      </div>
    </div>
  )
}
