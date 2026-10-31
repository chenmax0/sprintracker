import { useEffect, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router'
import { Card } from '../../lib/Card'
import { MemberLabel } from '../../lib/MemberLabel'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { useProject } from '../projects/hooks'
import { useSprints } from '../sprints/hooks'
import { useTeamMembers } from '../teams/hooks'
import type { BacklogSort } from '../tickets/api'
import { CreateTicketModal } from '../tickets/CreateTicketModal'
import { useBacklog, useMoveTicketToSprint } from '../tickets/hooks'
import { TicketPanel } from '../tickets/TicketPanel'
import { ageInDays, formatAge, STALE_AFTER_DAYS } from './age'

const PER_PAGE = 25

const SORT_OPTIONS: { value: BacklogSort; label: string }[] = [
  { value: 'oldest', label: "Plus anciens d'abord" },
  { value: 'newest', label: "Plus récents d'abord" },
  { value: 'most_carried_over', label: 'Les plus reportés' },
]

const controlClass =
  'rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none'

export function BacklogPage() {
  const { projectId } = useParams<{ projectId: string }>()
  if (!projectId) throw new Error('Missing projectId')

  // Filters live in the URL: a filtered backlog can be bookmarked or shared, and survives a reload.
  const [params, setParams] = useSearchParams()
  const search = params.get('q') ?? ''
  const assignee = params.get('assignee') ?? ''
  const sort = (SORT_OPTIONS.find((o) => o.value === params.get('sort'))?.value ?? 'oldest') satisfies BacklogSort
  const page = Math.max(1, Number(params.get('page')) || 1)

  const [searchInput, setSearchInput] = useState(search)
  const [showCreate, setShowCreate] = useState(false)
  const [selectedTicketId, setSelectedTicketId] = useState<string | null>(null)

  const { data: project } = useProject(projectId)
  const { data: sprints } = useSprints(projectId)
  const { data: members } = useTeamMembers(project?.teamId ?? '')
  const memberDirectory = toMemberDirectory(members)
  const activeSprint = sprints?.find((sprint) => sprint.status === 'active') ?? null
  const moveToSprint = useMoveTicketToSprint(projectId)

  const { data, isLoading, isError, isFetching } = useBacklog(projectId, {
    search,
    assignee,
    sort,
    page,
    perPage: PER_PAGE,
  })

  function update(changes: Record<string, string>, { resetPage = true } = {}) {
    setParams(
      (current) => {
        const next = new URLSearchParams(current)
        for (const [key, value] of Object.entries(changes)) {
          if (value === '' || (key === 'sort' && value === 'oldest') || (key === 'page' && value === '1')) {
            next.delete(key)
          } else {
            next.set(key, value)
          }
        }
        if (resetPage) next.delete('page')
        return next
      },
      { replace: true },
    )
  }

  // Wait for a pause in typing before querying the server.
  useEffect(() => {
    if (searchInput === search) return
    const timer = window.setTimeout(() => update({ q: searchInput.trim() }), 300)
    return () => window.clearTimeout(timer)
    // eslint-disable-next-line react-hooks/exhaustive-deps -- `update` only closes over setParams
  }, [searchInput, search])

  const total = data?.total ?? 0
  const pageCount = Math.max(1, Math.ceil(total / PER_PAGE))
  const hasFilters = search !== '' || assignee !== ''

  return (
    <div className="mx-auto flex max-w-6xl flex-col gap-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <Link to={`/projects/${projectId}`} className="text-sm text-indigo-600 hover:underline">
            ← {project?.name ?? 'Projet'}
          </Link>
          <h1 className="mt-2 text-2xl font-semibold text-gray-900">Backlog</h1>
          <p className="mt-1 text-sm text-gray-500">
            Les tickets qui n'appartiennent à aucun sprint, et ceux d'un sprint terminé qui attendent d'être reportés.
          </p>
        </div>
        <button
          type="button"
          onClick={() => setShowCreate(true)}
          className="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
        >
          Nouveau ticket
        </button>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <input
          type="search"
          value={searchInput}
          onChange={(e) => setSearchInput(e.target.value)}
          placeholder="Rechercher un titre ou un n° (#12)"
          aria-label="Rechercher dans le backlog"
          className={`${controlClass} w-72`}
        />
        <select
          value={assignee}
          onChange={(e) => update({ assignee: e.target.value })}
          aria-label="Filtrer par membre"
          className={controlClass}
        >
          <option value="">Tous les membres</option>
          <option value="none">Non assignés</option>
          {members?.map((member) => (
            <option key={member.memberId} value={member.memberId}>
              {member.name}
            </option>
          ))}
        </select>
        <select
          value={sort}
          onChange={(e) => update({ sort: e.target.value })}
          aria-label="Trier"
          className={controlClass}
        >
          {SORT_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        {hasFilters && (
          <button
            type="button"
            onClick={() => {
              setSearchInput('')
              update({ q: '', assignee: '' })
            }}
            className="text-sm text-indigo-600 hover:underline"
          >
            Réinitialiser les filtres
          </button>
        )}
        <span className="ml-auto text-sm text-gray-500">
          {total} ticket{total > 1 ? 's' : ''}
        </span>
      </div>

      {!activeSprint && (
        <p className="text-sm text-gray-500">Lancez un sprint pour pouvoir y ajouter des tickets du backlog.</p>
      )}
      {isError && <Card className="border-red-200 text-sm text-red-700">Impossible de charger le backlog.</Card>}
      {isLoading && <p className="text-sm text-gray-500">Chargement…</p>}

      {data && (
        <div className={`flex flex-col gap-4 transition-opacity ${isFetching ? 'opacity-60' : ''}`}>
          {data.items.length === 0 ? (
            <Card className="border-dashed text-center">
              <p className="text-sm text-gray-500">
                {hasFilters ? 'Aucun ticket ne correspond à ces filtres.' : 'Le backlog est vide.'}
              </p>
            </Card>
          ) : (
            <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-gray-200 bg-gray-50 text-xs text-gray-500">
                  <tr>
                    <th className="px-4 py-2.5 font-medium">Ticket</th>
                    <th className="px-4 py-2.5 font-medium">Assigné à</th>
                    <th className="px-4 py-2.5 font-medium">Créé</th>
                    <th className="px-4 py-2.5 font-medium">Reports</th>
                    <th className="px-4 py-2.5" />
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {data.items.map((ticket) => {
                    const days = ageInDays(ticket.createdAt)
                    return (
                      <tr key={ticket.id} className="hover:bg-gray-50">
                        <td className="max-w-md px-4 py-2.5">
                          <button
                            type="button"
                            onClick={() => setSelectedTicketId(ticket.id)}
                            className="flex w-full items-baseline gap-2 text-left"
                          >
                            <span className="shrink-0 text-gray-400">#{ticket.number}</span>
                            <span className="truncate text-gray-900 hover:text-indigo-600">{ticket.title}</span>
                          </button>
                        </td>
                        <td className="px-4 py-2.5 text-gray-600">
                          {ticket.assigneeId ? (
                            <MemberLabel memberId={ticket.assigneeId} directory={memberDirectory} />
                          ) : (
                            <span className="text-gray-400">—</span>
                          )}
                        </td>
                        <td
                          className={`whitespace-nowrap px-4 py-2.5 ${days >= STALE_AFTER_DAYS ? 'font-medium text-amber-700' : 'text-gray-600'}`}
                          title={new Date(ticket.createdAt).toLocaleDateString('fr-FR', { dateStyle: 'long' })}
                        >
                          {formatAge(days)}
                        </td>
                        <td className="px-4 py-2.5">
                          {ticket.carriedOverCount > 0 ? (
                            <span
                              className="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700"
                              title={`Reporté depuis ${ticket.carriedOverCount} sprint(s)`}
                            >
                              ↻ {ticket.carriedOverCount}
                            </span>
                          ) : (
                            <span className="text-gray-400">—</span>
                          )}
                        </td>
                        <td className="px-4 py-2.5 text-right">
                          <button
                            type="button"
                            disabled={!activeSprint || moveToSprint.isPending}
                            onClick={() =>
                              activeSprint && moveToSprint.mutate({ ticketId: ticket.id, sprintId: activeSprint.id })
                            }
                            className="whitespace-nowrap rounded-md border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                          >
                            Ajouter au sprint
                          </button>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
            </div>
          )}

          {pageCount > 1 && (
            <nav aria-label="Pagination" className="flex items-center justify-between text-sm text-gray-600">
              <button
                type="button"
                disabled={page <= 1}
                onClick={() => update({ page: String(page - 1) }, { resetPage: false })}
                className="rounded-md border border-gray-300 px-3 py-1.5 hover:bg-gray-50 disabled:opacity-50"
              >
                ← Précédent
              </button>
              <span>
                Page {page} sur {pageCount}
              </span>
              <button
                type="button"
                disabled={page >= pageCount}
                onClick={() => update({ page: String(page + 1) }, { resetPage: false })}
                className="rounded-md border border-gray-300 px-3 py-1.5 hover:bg-gray-50 disabled:opacity-50"
              >
                Suivant →
              </button>
            </nav>
          )}
        </div>
      )}

      {moveToSprint.isError && <p className="text-sm text-red-600">Erreur lors de l'ajout au sprint.</p>}

      {showCreate && (
        <CreateTicketModal
          projectId={projectId}
          activeSprintId={null}
          members={members ?? []}
          onClose={() => setShowCreate(false)}
        />
      )}
      {selectedTicketId && <TicketPanel ticketId={selectedTicketId} onClose={() => setSelectedTicketId(null)} />}
    </div>
  )
}
