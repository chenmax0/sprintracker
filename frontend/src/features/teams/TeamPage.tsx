import { type FormEvent, useState } from 'react'
import { Link, useParams } from 'react-router'
import { ApiError } from '../../lib/apiClient'
import { MemberLabel } from '../../lib/MemberLabel'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { useCreateProject, useProjects } from '../projects/hooks'
import { useAddTeamMember, useTeam, useTeamMembers } from './hooks'

export function TeamPage() {
  const { teamId } = useParams<{ teamId: string }>()
  if (!teamId) throw new Error('Missing teamId')

  const { data: team } = useTeam(teamId)
  const { data: members } = useTeamMembers(teamId)
  const memberDirectory = toMemberDirectory(members)
  const addMember = useAddTeamMember(teamId)
  const { data: projects } = useProjects(teamId)
  const createProject = useCreateProject(teamId)

  const [email, setEmail] = useState('')
  const [projectName, setProjectName] = useState('')

  function handleInvite(event: FormEvent) {
    event.preventDefault()
    addMember.mutate(email, { onSuccess: () => setEmail('') })
  }

  function handleCreateProject(event: FormEvent) {
    event.preventDefault()
    createProject.mutate(projectName, { onSuccess: () => setProjectName('') })
  }

  return (
    <div className="mx-auto flex max-w-4xl flex-col gap-10">
      <div>
        <Link to="/" className="text-sm text-indigo-600 hover:underline">
          ← Mes équipes
        </Link>
        <h1 className="mt-2 text-xl font-semibold text-gray-900">{team?.name}</h1>
      </div>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Membres</h2>
        <ul className="mb-3 flex flex-col gap-1">
          {members?.map((member) => (
            <li key={member.memberId} className="flex items-center gap-2 text-sm text-gray-700">
              <MemberLabel memberId={member.memberId} directory={memberDirectory} />
              <span className="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">{member.role}</span>
            </li>
          ))}
        </ul>
        <form onSubmit={handleInvite} className="flex max-w-sm gap-2">
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="Email à inviter"
            required
            className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
          />
          <button
            type="submit"
            disabled={addMember.isPending}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
          >
            Inviter
          </button>
        </form>
        {addMember.isError && (
          <p className="mt-2 text-sm text-red-600">
            {addMember.error instanceof ApiError ? addMember.error.message : "Erreur lors de l'invitation"}
          </p>
        )}
      </section>

      <section>
        <h2 className="mb-3 text-sm font-semibold text-gray-700">Projets</h2>
        <ul className="mb-3 flex flex-col gap-2">
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
        <form onSubmit={handleCreateProject} className="flex max-w-sm gap-2">
          <input
            type="text"
            value={projectName}
            onChange={(e) => setProjectName(e.target.value)}
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
            {createProject.error instanceof ApiError ? createProject.error.message : 'Seul le owner peut créer un projet.'}
          </p>
        )}
      </section>
    </div>
  )
}
