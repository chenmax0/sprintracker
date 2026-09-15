import { Link } from 'react-router'

export function LandingPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <header className="border-b border-gray-200 bg-white">
        <div className="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
          <span className="text-lg font-semibold text-gray-900">Sprintracker</span>
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

      <main className="mx-auto max-w-2xl px-6 py-24 text-center">
        <h1 className="text-4xl font-semibold tracking-tight text-gray-900">
          Un mini-Jira pour suivre vos sprints
        </h1>
        <p className="mt-4 text-lg text-gray-600">
          Équipes, projets, sprints, tickets et commentaires — l'essentiel pour piloter un sprint, sans le superflu.
        </p>
        <div className="mt-10 flex items-center justify-center gap-4">
          <Link
            to="/register"
            className="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-500"
          >
            Créer un compte
          </Link>
          <Link
            to="/demo"
            className="rounded-md border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Voir la démo
          </Link>
        </div>
      </main>
    </div>
  )
}
