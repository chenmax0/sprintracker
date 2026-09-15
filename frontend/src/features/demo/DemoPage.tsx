import { Link } from 'react-router'
import { toMemberDirectory } from '../../lib/memberDirectory'
import { DemoKanbanBoard } from './DemoKanbanBoard'
import { useDemoSnapshot } from './hooks'

export function DemoPage() {
  const { data, isLoading } = useDemoSnapshot()
  const memberDirectory = toMemberDirectory(data?.members)

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
          Mode démo — essayez le glisser-déposer, rien n'est sauvegardé.{' '}
          <Link to="/register" className="font-medium underline">
            Créez un compte
          </Link>{' '}
          pour gérer vos propres équipes et projets.
        </div>
      </div>

      <main className="px-6 py-8">
        {isLoading && <p className="mx-auto max-w-4xl text-gray-500">Chargement…</p>}

        {data?.project && (
          <>
            <h1 className="mx-auto mb-8 max-w-4xl text-xl font-semibold text-gray-900">{data.project.name}</h1>

            <DemoKanbanBoard tickets={data.tickets} sprints={data.sprints} memberDirectory={memberDirectory} />
          </>
        )}
      </main>
    </div>
  )
}
