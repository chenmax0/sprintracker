import { type FormEvent, useState } from 'react'
import { Link } from 'react-router'
import { ApiError } from '../../lib/apiClient'
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
    <div className="mx-auto max-w-4xl">
      <h1 className="mb-6 text-xl font-semibold text-gray-900">Mes projets</h1>

      {isLoading && <p className="text-gray-500">Chargement…</p>}

      <ul className="mb-8 flex flex-col gap-2">
        {projects?.map((project) => (
          <li key={project.id}>
            <Link
              to={`/projects/${project.id}`}
              className="block rounded-md border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-900 hover:border-indigo-300 hover:bg-indigo-50"
            >
              {project.name}
            </Link>
          </li>
        ))}
        {projects?.length === 0 && <p className="text-sm text-gray-500">Aucun projet pour l'instant.</p>}
      </ul>

      <form onSubmit={handleSubmit} className="flex max-w-sm gap-2">
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nom du nouveau projet"
          required
          className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
        />
        <button
          type="submit"
          disabled={createProject.isPending}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Créer
        </button>
      </form>
      {createProject.isError && (
        <p className="mt-2 text-sm text-red-600">
          {createProject.error instanceof ApiError ? createProject.error.message : 'Erreur lors de la création'}
        </p>
      )}
    </div>
  )
}
