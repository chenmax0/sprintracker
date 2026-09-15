import { type FormEvent, useState } from 'react'
import { Link } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { useCreateTeam, useTeams } from './hooks'

export function TeamsPage() {
  const { data: teams, isLoading } = useTeams()
  const createTeam = useCreateTeam()
  const [name, setName] = useState('')

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    createTeam.mutate(name, { onSuccess: () => setName('') })
  }

  return (
    <div>
      <h1 className="mb-6 text-xl font-semibold text-gray-900">Mes équipes</h1>

      {isLoading && <p className="text-gray-500">Chargement…</p>}

      <ul className="mb-8 flex flex-col gap-2">
        {teams?.map((team) => (
          <li key={team.id}>
            <Link
              to={`/teams/${team.id}`}
              className="block rounded-md border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-900 hover:border-indigo-300 hover:bg-indigo-50"
            >
              {team.name}
            </Link>
          </li>
        ))}
        {teams?.length === 0 && <p className="text-sm text-gray-500">Aucune équipe pour l'instant.</p>}
      </ul>

      <form onSubmit={handleSubmit} className="flex max-w-sm gap-2">
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nom de la nouvelle équipe"
          required
          className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
        />
        <button
          type="submit"
          disabled={createTeam.isPending}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          Créer
        </button>
      </form>
      {createTeam.isError && (
        <p className="mt-2 text-sm text-red-600">
          {createTeam.error instanceof ApiError ? createTeam.error.message : 'Erreur lors de la création'}
        </p>
      )}
    </div>
  )
}
