import { Link } from 'react-router'
import { useDemoSnapshot } from './hooks'

export function DemoPage() {
  const { data, isLoading } = useDemoSnapshot()

  const backlogTickets = data?.tickets.filter((t) => t.sprintId === null) ?? []
  const sprintTickets = (sprintId: string) => data?.tickets.filter((t) => t.sprintId === sprintId) ?? []

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="border-b border-gray-200 bg-white">
        <div className="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
          <Link to="/" className="text-lg font-semibold text-gray-900">
            Sprintracker
          </Link>
          <div className="flex items-center gap-3">
            <Link to="/login" className="text-sm text-gray-700 hover:underline">
              Se connecter
            </Link>
            <Link
              to="/register"
              className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
            >
              Créer un compte
            </Link>
          </div>
        </div>
      </header>

      <div className="border-b border-indigo-100 bg-indigo-50">
        <div className="mx-auto max-w-4xl px-6 py-3 text-sm text-indigo-800">
          Mode démo — lecture seule.{' '}
          <Link to="/register" className="font-medium underline">
            Créez un compte
          </Link>{' '}
          pour gérer vos propres équipes et projets.
        </div>
      </div>

      <main className="mx-auto max-w-4xl px-6 py-8">
        {isLoading && <p className="text-gray-500">Chargement…</p>}

        {data?.project && (
          <>
            <h1 className="mb-8 text-xl font-semibold text-gray-900">{data.project.name}</h1>

            <div className="flex flex-col gap-8">
              {data.sprints.map((sprint) => (
                <section key={sprint.id}>
                  <h2 className="mb-3 text-sm font-semibold text-gray-700">
                    {sprint.name}{' '}
                    <span className="font-normal text-gray-400">
                      ({sprint.startDate} → {sprint.endDate})
                    </span>
                  </h2>
                  <ul className="flex flex-col gap-2">
                    {sprintTickets(sprint.id).map((ticket) => (
                      <li
                        key={ticket.id}
                        className="flex items-center justify-between rounded-md border border-gray-200 bg-white px-4 py-3 text-sm"
                      >
                        <span className="text-gray-900">{ticket.title}</span>
                        <span className="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">{ticket.status}</span>
                      </li>
                    ))}
                  </ul>
                </section>
              ))}

              <section>
                <h2 className="mb-3 text-sm font-semibold text-gray-700">Backlog</h2>
                <ul className="flex flex-col gap-2">
                  {backlogTickets.map((ticket) => (
                    <li
                      key={ticket.id}
                      className="flex items-center justify-between rounded-md border border-gray-200 bg-white px-4 py-3 text-sm"
                    >
                      <span className="text-gray-900">{ticket.title}</span>
                      <span className="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">{ticket.status}</span>
                    </li>
                  ))}
                  {backlogTickets.length === 0 && <p className="text-sm text-gray-500">Backlog vide.</p>}
                </ul>
              </section>
            </div>
          </>
        )}
      </main>
    </div>
  )
}
